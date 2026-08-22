import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { useAuth } from '@/features/auth/auth-context'
import { useEnums } from '@/lib/api/enums'
import type { Incident } from '@/lib/api/types'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill } from '@/components/StatusPill'
import { Modal } from '@/components/Modal'
import { Icons } from '@/components/icons'
import { useIncidents, useConsulterIncident, useResoudreIncident, useDemanderRestitution, useTraiterRetour, useAssignerIncident, useTechniciens, useIncidentCommentaires, useAjouterCommentaire, useReouvrirIncident, useSupprimerIncident, type IncidentFilters } from './api'
import { IncidentForm } from './IncidentForm'
import { useEquipements } from '@/features/equipements/api'

type RetourMotif = 'MAINTENANCE_SUR_PLACE' | 'NOUVELLE_DATE' | 'POSTE_REMPLACE'
// Étape interne supplémentaire du motif "Nouvelle date" : après avoir choisi
// la date/heure de restitution, le technicien peut affecter un poste
// remplaçant temporaire à l'employé pour patienter.
type RetourStep = 'pick' | RetourMotif | 'NOUVELLE_DATE_EQUIPEMENT'

// Date/heure locale actuelle au format attendu par <input type="datetime-local">,
// arrondie à la minute SUPÉRIEURE. Un simple troncage (vers le bas) produirait
// une valeur déjà dans le passé au moment de la soumission (les secondes sont
// perdues par le champ datetime-local), ce qui ferait échouer silencieusement
// la validation serveur "after_or_equal:now" — d'où l'arrondi vers le haut.
function nowAsDatetimeLocal(): string {
  const d = new Date()
  d.setSeconds(0, 0)
  d.setMinutes(d.getMinutes() + 1)
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

export function IncidentsPage() {
  const { t, i18n } = useTranslation()
  const { user } = useAuth()
  const isAdmin = user?.role === 'SUPER_ADMIN' || user?.role === 'ADMIN'
  const isTechnicien = user?.role === 'TECHNICIEN'
  const isEmploye = user?.role === 'EMPLOYE'
  const isStaff = isAdmin || isTechnicien

  // Un membre du staff peut lui-même être concerné par un incident (son
  // propre poste) : "Mes propres incidents" bascule alors vers la même vue
  // simplifiée qu'un employé, séparée de la vue de gestion professionnelle.
  const [vueMesIncidents, setVueMesIncidents] = useState(false)

  const { data: enums } = useEnums()
  const { data: techniciens } = useTechniciens()
  const [filters, setFilters] = useState<IncidentFilters>({ page: 1 })
  const [search, setSearch] = useState('')
  const { data, isLoading } = useIncidents({ ...filters, mesIncidents: isStaff && vueMesIncidents ? true : undefined })

  const [reportOpen, setReportOpen] = useState(false)
  const [viewing, setViewing] = useState<Incident | null>(null)
  const [reopening, setReopening] = useState<Incident | null>(null)
  const [reopenMessage, setReopenMessage] = useState('')
  const [deleting, setDeleting] = useState<Incident | null>(null)
  const [deleteComment, setDeleteComment] = useState('')
  const [resolving, setResolving] = useState<Incident | null>(null)
  const [resolveStep, setResolveStep] = useState<'choice' | 'ramener' | 'garder' | 'direct'>('choice')
  const [motif, setMotif] = useState<'aucun_probleme' | 'suivre_etapes' | null>(null)
  const [etapes, setEtapes] = useState('')
  const [dateRestitution, setDateRestitution] = useState('')
  const [solutionDirecte, setSolutionDirecte] = useState('')
  const [ramenerError, setRamenerError] = useState<string | null>(null)
  const [assigning, setAssigning] = useState<Incident | null>(null)
  const [technicienId, setTechnicienId] = useState<string>('')

  const [retour, setRetour] = useState<Incident | null>(null)
  const [retourStep, setRetourStep] = useState<RetourStep>('pick')
  const [retourCommentaire, setRetourCommentaire] = useState('')
  const [retourDate, setRetourDate] = useState('')
  const [retourEquipementId, setRetourEquipementId] = useState('')
  const [retourEquipementRemplacementId, setRetourEquipementRemplacementId] = useState('')
  const [retourError, setRetourError] = useState<string | null>(null)

  const consulter = useConsulterIncident()
  const resoudre = useResoudreIncident()
  const demanderRestitution = useDemanderRestitution()
  const traiterRetour = useTraiterRetour()
  const reouvrir = useReouvrirIncident()
  const supprimer = useSupprimerIncident()
  const assigner = useAssignerIncident()
  const { data: equipementsDispo } = useEquipements({
    type: retour?.equipement?.type,
    localisation: retour?.equipement?.localisation ?? undefined,
    statutAffectation: 'disponible',
  })

  // Technicien(s) éligibles pour l'assignation : ceux du même site que
  // l'équipement, en excluant le technicien concerné par l'incident lui-même
  // — sauf s'il est le seul technicien du site, auquel cas il reste la seule
  // option possible (voir la même règle côté serveur dans assigner()).
  const techniciensSite = (techniciens ?? []).filter((tech) => {
    const locEquipement = assigning?.equipement?.localisation
    return !locEquipement || tech.localisation === locEquipement
  })
  const techniciensAutres = techniciensSite.filter((tech) => tech.id !== assigning?.employeId)
  const techniciensOptions = techniciensAutres.length > 0 ? techniciensAutres : techniciensSite

  const rows = data?.data ?? []

  // Debounce the search box into the query filter.
  useEffect(() => {
    const id = setTimeout(() => setFilters((f) => ({ ...f, q: search, page: 1 })), 350)
    return () => clearTimeout(id)
  }, [search])

  // Force a re-render every 30s so "date de restitution atteinte" (and the
  // Résoudre/Confirmer réception buttons that depend on it) update on their
  // own once the deadline passes, without needing a manual page refresh.
  const [, forceTick] = useState(0)
  useEffect(() => {
    const id = setInterval(() => forceTick((n) => n + 1), 30_000)
    return () => clearInterval(id)
  }, [])

  // The <input type="datetime-local"> value has no timezone info — it is the
  // user's local wall-clock time. Converting it through a JS Date and back to
  // ISO anchors it to the correct UTC instant, so the backend (which parses
  // dates in UTC) stores the time the user actually picked rather than
  // shifting it by the server/browser timezone difference.
  function localDateTimeToIso(value: string): string {
    return new Date(value).toISOString()
  }

  // Extrait un message d'erreur lisible d'une réponse 422/erreur Axios, pour
  // ne jamais laisser un échec de soumission passer inaperçu côté technicien.
  function extractErrorMessage(err: unknown): string {
    if (isAxiosError(err)) {
      const errors = err.response?.data?.errors as Record<string, string[]> | undefined
      const first = errors ? Object.values(errors)[0]?.[0] : undefined
      return first ?? err.response?.data?.message ?? "Une erreur est survenue, merci de réessayer."
    }
    return "Une erreur est survenue, merci de réessayer."
  }

function closeResolve() {
    setResolving(null)
    setResolveStep('choice')
    setMotif(null)
    setEtapes('')
    setDateRestitution('')
    setSolutionDirecte('')
    setRamenerError(null)
  }

  function openResolve(incident: Incident) {
    setResolving(incident)
    setMotif(null)
    setEtapes('')
    setDateRestitution('')
    setSolutionDirecte('')
    setRamenerError(null)
    setResolveStep(incident.statut === 'EN_MAINTENANCE' || incident.dateRestitutionPrevue ? 'direct' : 'choice')
  }

  async function submitRamener(e: React.FormEvent) {
    e.preventDefault()
    if (!resolving || !dateRestitution) return
    setRamenerError(null)
    try {
      await demanderRestitution.mutateAsync({ id: resolving.id, dateRestitution: localDateTimeToIso(dateRestitution) })
      closeResolve()
    } catch (err) {
      setRamenerError(extractErrorMessage(err))
    }
  }

  async function submitGarder(e: React.FormEvent) {
    e.preventDefault()
    if (!resolving || !motif) return
    const solution =
      motif === 'aucun_probleme'
        ? 'Aucun problème avéré : le poste fonctionne normalement.'
        : `Aucune intervention nécessaire. Étapes à suivre : ${etapes}`
    await resoudre.mutateAsync({ id: resolving.id, solution })
    closeResolve()
  }

  async function submitDirect(e: React.FormEvent) {
    e.preventDefault()
    if (!resolving || !solutionDirecte) return
    await resoudre.mutateAsync({ id: resolving.id, solution: solutionDirecte })
    closeResolve()
  }

  function openRetour(incident: Incident) {
    setRetour(incident)
    setRetourStep('pick')
    setRetourCommentaire('')
    setRetourDate('')
    setRetourEquipementId('')
    setRetourEquipementRemplacementId('')
    setRetourError(null)
  }

  function closeRetour() {
    setRetour(null)
    setRetourStep('pick')
    setRetourCommentaire('')
    setRetourDate('')
    setRetourEquipementId('')
    setRetourEquipementRemplacementId('')
    setRetourError(null)
  }

  // Choix du motif : pour "Nouvelle date", la date de restitution est
  // pré-remplie avec la date/heure actuelle (modifiable par le technicien).
  function pickRetourMotif(motif: RetourMotif) {
    setRetourStep(motif)
    if (motif === 'NOUVELLE_DATE') setRetourDate(nowAsDatetimeLocal())
  }

  async function submitRetour(e: React.FormEvent) {
    e.preventDefault()
    if (!retour) return
    if (retourStep === 'pick' || retourStep === 'NOUVELLE_DATE') return
    if (retourStep === 'POSTE_REMPLACE' && !retourEquipementId) return

    const motif: RetourMotif = retourStep === 'NOUVELLE_DATE_EQUIPEMENT' ? 'NOUVELLE_DATE' : retourStep

    setRetourError(null)
    try {
      await traiterRetour.mutateAsync({
        id: retour.id,
        motif,
        commentaire: retourCommentaire || undefined,
        dateRestitution: motif === 'NOUVELLE_DATE' ? localDateTimeToIso(retourDate) : undefined,
        nouvelEquipementId: retourStep === 'POSTE_REMPLACE' ? Number(retourEquipementId) : undefined,
        nouvelEquipementRemplacementId:
          retourStep === 'NOUVELLE_DATE_EQUIPEMENT' && retourEquipementRemplacementId
            ? Number(retourEquipementRemplacementId)
            : undefined,
      })
      closeRetour()
    } catch (err) {
      setRetourError(extractErrorMessage(err))
    }
  }
  async function submitAssigner(e: React.FormEvent) {
    e.preventDefault()
    if (!assigning || !technicienId) return
    await assigner.mutateAsync({ id: assigning.id, technicienId: Number(technicienId) })
    setAssigning(null)
    setTechnicienId('')
  }
  async function submitReouvrir(e: React.FormEvent) {
    e.preventDefault()
    if (!reopening || !reopenMessage.trim()) return
    await reouvrir.mutateAsync({ id: reopening.id, message: reopenMessage })
    setReopening(null)
    setReopenMessage('')
  }

  async function submitSupprimer(e: React.FormEvent) {
    e.preventDefault()
    if (!deleting || !deleteComment.trim()) return
    await supprimer.mutateAsync({ id: deleting.id, commentaire: deleteComment })
    setDeleting(null)
    setDeleteComment('')
  }

  // Admin/super admin : ouvrir "Consulter" sur un incident encore "Ouvert"
  // le fait automatiquement passer à "En cours" côté serveur.
  async function handleConsulter(x: Incident) {
    if (isAdmin && x.statut === 'OUVERT') {
      const updated = await consulter.mutateAsync(x.id)
      setViewing(updated)
      return
    }
    setViewing(x)
  }

  function dateRestitutionAtteinte(x: Incident) {
    return !!x.dateRestitutionPrevue && new Date(x.dateRestitutionPrevue) <= new Date()
  }

  function canReopen(x: Incident) {
    if (x.statut !== 'RESOLU' || !x.dateResolution) return false
    const deadline = new Date(new Date(x.dateResolution).getTime() + 5 * 24 * 60 * 60 * 1000)
    return new Date() <= deadline
  }
  // — Vue Employé (ou "Mes propres incidents" pour le staff) : ses incidents
  // directement, avec bouton de déclaration ——————————————————————————
  if (isEmploye || (isStaff && vueMesIncidents)) {
    return (
      <>
        <PageHeader
          eyebrow={t('app.name')}
          title={isStaff ? 'Mes propres incidents' : t('incidents.title')}
          subtitle={t('incidents.subtitle')}
          actions={
            <>
              {isStaff && (
                <button className="btn" onClick={() => { setVueMesIncidents(false); setFilters({ page: 1 }); setSearch('') }}>
                  {t('incidents.tabGestion')}
                </button>
              )}
              <button className="btn btn-primary" onClick={() => setReportOpen(true)}>
                <Icons.plus size={16} />
                {t('incidents.report')}
              </button>
            </>
          }
        />
        <section className="panel">
          <div className="flex flex-wrap items-center gap-3 border-b border-[var(--color-line)] p-3">
            <div className="relative min-w-56 flex-1">
              <span className="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--color-faint)]">
                <Icons.search size={15} />
              </span>
              <input
                className="input pl-8"
                placeholder="Rechercher (référence, titre...)"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <select className="input w-auto" value={filters.statut ?? ''} onChange={(e) => setFilters((f) => ({ ...f, statut: e.target.value, page: 1 }))}>
              <option value="">{t('incidents.allStatuts')}</option>
              {enums?.statutIncident.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
          </div>
          <div className="overflow-x-auto">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Référence</th>
                  <th>{t('incidents.cols.titre')}</th>
                  <th>{t('incidents.cols.equipement')}</th>
                  <th>{t('incidents.cols.statut')}</th>
                  <th>{t('incidents.cols.date')}</th>
                  <th className="text-right">{t('incidents.cols.actions')}</th>
                </tr>
              </thead>
              <tbody>
                {rows.map((x) => (
                  <tr key={x.id}>
                    <td className="mono text-[11px] text-[var(--color-muted)]">{x.reference}</td>
                    <td>
                      <div className="font-medium">{x.titre}</div>
                      <div className="max-w-xs truncate text-[11px] text-[var(--color-faint)]">{x.description}</div>
                    </td>
                    <td className="mono text-[var(--color-muted)]">{x.equipement?.nom ?? '—'}</td>
                    <td><StatusPill value={x.statut} label={x.statutLabel} /></td>
                    <td className="mono text-[11px] text-[var(--color-faint)]">
                      {new Date(x.dateSignalement).toLocaleDateString(i18n.language, { dateStyle: 'short' })}
                    </td>
                    <td>
                      <div className="flex items-center justify-end gap-2">
                        <button className="btn px-2.5 py-1 text-xs" onClick={() => setViewing(x)}>
                          Consulter
                        </button>
                        {canReopen(x) && (
                          <button className="btn btn-primary px-2.5 py-1 text-xs" onClick={() => { setReopening(x); setReopenMessage('') }}>
                            {t('incidents.reopen')}
                          </button>
                        )}
                        {x.statut === 'OUVERT' && (
                          <button
                            className="btn px-2.5 py-1 text-xs"
                            style={{ color: '#ff8983' }}
                            onClick={() => { setDeleting(x); setDeleteComment('') }}
                          >
                            {t('incidents.delete')}
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
               {!isLoading && rows.length === 0 && (
                  <tr><td colSpan={6} className="py-12 text-center text-[var(--color-muted)]">{t('incidents.empty')}</td></tr>
                )}
                {isLoading && (
                  <tr><td colSpan={6} className="py-12 text-center text-[var(--color-faint)]">{t('common.loading')}</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </section>

        {reportOpen && <IncidentForm open={reportOpen} onClose={() => setReportOpen(false)} />}

       {viewing && (
          <IncidentDetailModal incident={viewing} onClose={() => setViewing(null)} locale={i18n.language} />
        )}

        <Modal open={!!reopening} onClose={() => setReopening(null)} title={t('incidents.reopen')} width={460}>
          <form onSubmit={submitReouvrir} className="space-y-4">
            <p className="text-sm text-[var(--color-muted)]">{reopening?.reference} — {reopening?.titre}</p>
            <p className="text-[13px] text-[var(--color-muted)]">{t('incidents.reopenHint')}</p>
            <div>
              <label className="field-label">{t('incidents.reopenMessageLabel')}</label>
              <textarea className="input" rows={4} value={reopenMessage} onChange={(e) => setReopenMessage(e.target.value)} required />
            </div>
            <div className="flex justify-end gap-2">
              <button type="button" className="btn" onClick={() => setReopening(null)}>Annuler</button>
              <button type="submit" className="btn btn-primary" disabled={reouvrir.isPending || !reopenMessage.trim()}>
                {reouvrir.isPending ? t('incidents.reopening') : t('incidents.reopenConfirm')}
              </button>
            </div>
          </form>
        </Modal>

        <Modal open={!!deleting} onClose={() => setDeleting(null)} title={t('incidents.deleteTitle')} width={460}>
          <form onSubmit={submitSupprimer} className="space-y-4">
            <p className="text-sm text-[var(--color-muted)]">{deleting?.reference} — {deleting?.titre}</p>
            <p className="text-[13px] text-[var(--color-muted)]">{t('incidents.deleteHint')}</p>
            <div>
              <label className="field-label">{t('incidents.deleteCommentLabel')}</label>
              <textarea className="input" rows={4} value={deleteComment} onChange={(e) => setDeleteComment(e.target.value)} required />
            </div>
            <div className="flex justify-end gap-2">
              <button type="button" className="btn" onClick={() => setDeleting(null)}>{t('incidents.deleteCancel')}</button>
              <button type="submit" className="btn btn-primary" disabled={supprimer.isPending || !deleteComment.trim()}>
                {supprimer.isPending ? t('incidents.deleting') : t('incidents.deleteConfirm')}
              </button>
            </div>
          </form>
        </Modal>
      </>
    )
  }

  // — Vue Admin / Super Admin / Technicien : tableau complet ———————————
  return (
    <>
      <PageHeader
        eyebrow={t('app.name')}
        title={t('incidents.title')}
        subtitle={t('incidents.subtitle')}
        actions={
          <>
            {isStaff && (
              <button className="btn" onClick={() => { setVueMesIncidents(true); setFilters({ page: 1 }); setSearch('') }}>
                {t('incidents.tabMesIncidents')}
              </button>
            )}
            <button className="btn btn-primary" onClick={() => setReportOpen(true)}>
              <Icons.plus size={16} />
              {t('incidents.report')}
            </button>
          </>
        }
      />

      <section className="panel">
        <div className="flex flex-wrap items-center gap-3 border-b border-[var(--color-line)] p-3">
          <div className="relative min-w-56 flex-1">
            <span className="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--color-faint)]">
              <Icons.search size={15} />
            </span>
            <input
              className="input pl-8"
              placeholder="Rechercher (référence, titre...)"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <select className="input w-auto" value={filters.statut ?? ''} onChange={(e) => setFilters((f) => ({ ...f, statut: e.target.value, page: 1 }))}>
            <option value="">{t('incidents.allStatuts')}</option>
            {enums?.statutIncident.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
          <select className="input w-auto" value={filters.priorite ?? ''} onChange={(e) => setFilters((f) => ({ ...f, priorite: e.target.value, page: 1 }))}>
            <option value="">{t('incidents.allPriorites')}</option>
            {enums?.severite.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
          <select className="input w-auto" value={filters.origine ?? ''} onChange={(e) => setFilters((f) => ({ ...f, origine: (e.target.value || undefined) as IncidentFilters['origine'], page: 1 }))}>
            <option value="">{t('incidents.allOrigines')}</option>
            <option value="employe">{t('incidents.origineEmploye')}</option>
            <option value="personnel">{t('incidents.originePersonnel')}</option>
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="data-table">
            <thead>
              <tr>
                <th>Référence</th>
                <th>{t('incidents.cols.titre')}</th>
                <th>{t('incidents.cols.equipement')}</th>
                <th>{t('incidents.cols.priorite')}</th>
                <th>{t('incidents.cols.statut')}</th>
                <th>{t('incidents.cols.signalePar')}</th>
                <th>{t('incidents.cols.traitePar')}</th>
                <th>{t('incidents.cols.date')}</th>
                <th className="text-right">{t('incidents.cols.actions')}</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((x) => (
                <tr key={x.id}>
                  <td className="mono text-[11px] text-[var(--color-muted)]">{x.reference}</td>
                  <td>
                    <div className="font-medium">{x.titre}</div>
                    <div className="max-w-xs truncate text-[11px] text-[var(--color-faint)]">{x.description}</div>
                  </td>
                  <td className="mono text-[var(--color-muted)]">{x.equipement?.nom ?? '—'}</td>
                  <td><StatusPill value={x.priorite} label={x.prioriteLabel} /></td>
                  <td><StatusPill value={x.statut} label={x.statutLabel} /></td>
                  <td className="text-[var(--color-muted)]">
                    <div className="flex items-center gap-1.5">
                      <span>{x.signalePar ?? '—'}</span>
                      {x.signaleParRole && x.signaleParRole !== 'EMPLOYE' && (
                        <StatusPill value={x.signaleParRole} label={x.signaleParRoleLabel ?? x.signaleParRole} />
                      )}
                    </div>
                  </td>
                  <td className="text-[var(--color-muted)]">{x.traitePar ?? '—'}</td>
                  <td className="mono text-[11px] text-[var(--color-faint)]">
                    {new Date(x.dateSignalement).toLocaleDateString(i18n.language, { dateStyle: 'short' })}
                  </td>
                  <td>
                    <div className="flex items-center justify-end gap-2">
                      <button
                        className="btn px-2.5 py-1 text-xs"
                        onClick={() => handleConsulter(x)}
                      >
                        Consulter
                      </button>
                      {isAdmin && !x.traitePar && x.statut !== 'RESOLU' && (
                        <button
                          className="btn px-2.5 py-1 text-xs"
                          onClick={() => { setAssigning(x); setTechnicienId('') }}
                        >
                          Assigner
                        </button>
                      )}
                      {user?.role === 'TECHNICIEN' && x.traitePar !== null && x.statut === 'EN_COURS' && x.dateRestitutionPrevue && !x.dateReceptionPoste && (
                        <button className="btn px-2.5 py-1 text-xs" onClick={() => openRetour(x)}>
                          {t('incidents.confirmReception')}
                        </button>
                      )}
                      {user?.role === 'TECHNICIEN' && x.traitePar !== null &&
                        (x.statut === 'OUVERT' ||
                          (x.statut === 'EN_COURS' && (!x.dateRestitutionPrevue || dateRestitutionAtteinte(x))) ||
                          x.statut === 'EN_MAINTENANCE') && (
  <button className="btn btn-primary px-2.5 py-1 text-xs" onClick={() => openResolve(x)}>
    {t('incidents.resolve')}
  </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
              {!isLoading && rows.length === 0 && (
                <tr><td colSpan={9} className="py-12 text-center text-[var(--color-muted)]">{t('incidents.empty')}</td></tr>
              )}
              {isLoading && (
                <tr><td colSpan={9} className="py-12 text-center text-[var(--color-faint)]">{t('common.loading')}</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </section>

      {reportOpen && <IncidentForm open={reportOpen} onClose={() => setReportOpen(false)} />}

      {viewing && (
        <IncidentDetailModal incident={viewing} onClose={() => setViewing(null)} locale={i18n.language} />
      )}

      <Modal
        open={!!resolving}
        onClose={closeResolve}
        title={resolveStep === 'direct' ? t('incidents.resolveForm.directTitle') : t('incidents.resolveForm.title')}
        width={460}
      >
        <div className="space-y-4">
          <p className="text-sm text-[var(--color-muted)]">{resolving?.reference} — {resolving?.titre}</p>

          {resolveStep === 'choice' && (
            <div className="space-y-2">
              <p className="field-label">{t('incidents.resolveForm.chooseAction')}</p>
              <button
                type="button"
                className="btn w-full justify-start"
                onClick={() => {
                  setResolveStep('ramener')
                  setDateRestitution(nowAsDatetimeLocal())
                }}
              >
                {t('incidents.resolveForm.ramenerPosteBtn')}
              </button>
              <button type="button" className="btn w-full justify-start" onClick={() => setResolveStep('garder')}>
                {t('incidents.resolveForm.garderPosteBtn')}
              </button>
              <div className="flex justify-end pt-2">
                <button type="button" className="btn" onClick={closeResolve}>{t('incidents.resolveForm.cancel')}</button>
              </div>
            </div>
          )}

          {resolveStep === 'ramener' && (
            <form onSubmit={submitRamener} className="space-y-4">
              <div>
                <label className="field-label">{t('incidents.resolveForm.dateRestitutionLabel')}</label>
                <input
                  type="datetime-local"
                  className="input"
                  value={dateRestitution}
                  onChange={(e) => setDateRestitution(e.target.value)}
                  required
                />
                <p className="mt-1 text-xs text-[var(--color-faint)]">{t('incidents.retourForm.newDateHint')}</p>
                {ramenerError && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{ramenerError}</p>}
              </div>
              <div className="flex justify-end gap-2">
                <button type="button" className="btn" onClick={() => setResolveStep('choice')}>{t('incidents.resolveForm.back')}</button>
                <button type="submit" className="btn btn-primary" disabled={demanderRestitution.isPending}>
                  {t('incidents.resolveForm.confirmRamener')}
                </button>
              </div>
            </form>
          )}

          {resolveStep === 'garder' && (
            <form onSubmit={submitGarder} className="space-y-4">
              <div className="space-y-2">
                <p className="field-label">{t('incidents.resolveForm.motifTitle')}</p>
                <label className="flex items-start gap-2 text-sm">
                  <input type="radio" name="motif" checked={motif === 'aucun_probleme'} onChange={() => setMotif('aucun_probleme')} />
                  {t('incidents.resolveForm.motifAucunProbleme')}
                </label>
                <label className="flex items-start gap-2 text-sm">
                  <input type="radio" name="motif" checked={motif === 'suivre_etapes'} onChange={() => setMotif('suivre_etapes')} />
                  {t('incidents.resolveForm.motifSuivreEtapes')}
                </label>
              </div>

              {motif === 'suivre_etapes' && (
                <div>
                  <label className="field-label">{t('incidents.resolveForm.etapesLabel')}</label>
                  <textarea className="input" rows={4} value={etapes} onChange={(e) => setEtapes(e.target.value)} required />
                </div>
              )}

              <div className="flex justify-end gap-2">
                <button type="button" className="btn" onClick={() => setResolveStep('choice')}>{t('incidents.resolveForm.back')}</button>
                <button type="submit" className="btn btn-primary" disabled={resoudre.isPending || !motif}>
                  {resoudre.isPending ? t('incidents.resolveForm.submitting') : t('incidents.resolveForm.submit')}
                </button>
              </div>
            </form>
          )}

          {resolveStep === 'direct' && (
            <form onSubmit={submitDirect} className="space-y-4">
              <div>
                <label className="field-label">{t('incidents.resolveForm.directLabel')}</label>
                <textarea className="input" rows={4} value={solutionDirecte} onChange={(e) => setSolutionDirecte(e.target.value)} required />
              </div>
              <div className="flex justify-end gap-2">
                <button type="button" className="btn" onClick={closeResolve}>{t('incidents.resolveForm.cancel')}</button>
                <button type="submit" className="btn btn-primary" disabled={resoudre.isPending}>
                  {resoudre.isPending ? t('incidents.resolveForm.submitting') : t('incidents.resolveForm.directSubmit')}
                </button>
              </div>
            </form>
          )}
        </div>
      </Modal>

      <Modal open={!!retour} onClose={closeRetour} title={t('incidents.retourForm.title')} width={480}>
        <div className="space-y-4">
          <p className="text-sm text-[var(--color-muted)]">{retour?.reference} — {retour?.titre}</p>

          {retourStep === 'pick' && (
            <div className="space-y-2">
              <p className="field-label">{t('incidents.retourForm.chooseMotif')}</p>
              {enums?.motifRetourPoste.map((o) => (
                <button
                  key={o.value}
                  type="button"
                  className="btn w-full justify-start"
                  onClick={() => pickRetourMotif(o.value as RetourMotif)}
                >
                  {o.label}
                </button>
              ))}
              <div className="flex justify-end pt-2">
                <button type="button" className="btn" onClick={closeRetour}>{t('incidents.retourForm.cancel')}</button>
              </div>
            </div>
          )}

          {retourStep === 'NOUVELLE_DATE' && (
            <div className="space-y-4">
              <div>
                <label className="field-label">{t('incidents.retourForm.newDate')}</label>
                <input
                  type="datetime-local"
                  className="input"
                  value={retourDate}
                  onChange={(e) => setRetourDate(e.target.value)}
                  required
                />
                <p className="mt-1 text-xs text-[var(--color-faint)]">{t('incidents.retourForm.newDateHint')}</p>
              </div>

              <div className="flex justify-between gap-2">
                <button type="button" className="btn" onClick={() => setRetourStep('pick')}>{t('incidents.retourForm.back')}</button>
                <button
                  type="button"
                  className="btn btn-primary"
                  disabled={!retourDate}
                  onClick={() => setRetourStep('NOUVELLE_DATE_EQUIPEMENT')}
                >
                  {t('incidents.retourForm.next')}
                </button>
              </div>
            </div>
          )}

          {retourStep === 'NOUVELLE_DATE_EQUIPEMENT' && (
            <form onSubmit={submitRetour} className="space-y-4">
              <div>
                <label className="field-label">{t('incidents.retourForm.newEquipementTemp')}</label>
                <p className="mb-1 text-xs text-[var(--color-faint)]">{t('incidents.retourForm.newEquipementTempHint')}</p>
                <select
                  className="input"
                  value={retourEquipementRemplacementId}
                  onChange={(e) => setRetourEquipementRemplacementId(e.target.value)}
                >
                  <option value="">{t('incidents.retourForm.noneOption')}</option>
                  {(equipementsDispo?.data ?? []).map((eq) => (
                    <option key={eq.id} value={eq.id}>{eq.nom}</option>
                  ))}
                </select>
                {equipementsDispo?.data?.length === 0 && (
                  <p className="mt-1 text-xs text-[var(--color-faint)]">Aucun poste disponible du même type sur ce site.</p>
                )}
              </div>

              <div>
                <label className="field-label">{t('incidents.retourForm.commentaire')}</label>
                <textarea
                  className="input"
                  rows={3}
                  value={retourCommentaire}
                  onChange={(e) => setRetourCommentaire(e.target.value)}
                />
              </div>

              {retourError && <p className="text-xs" style={{ color: '#ff8983' }}>{retourError}</p>}

              <div className="flex justify-between gap-2">
                <button type="button" className="btn" onClick={() => setRetourStep('NOUVELLE_DATE')}>{t('incidents.retourForm.back')}</button>
                <button type="submit" className="btn btn-primary" disabled={traiterRetour.isPending}>
                  {traiterRetour.isPending ? t('incidents.retourForm.submitting') : t('incidents.retourForm.submit')}
                </button>
              </div>
            </form>
          )}

          {(retourStep === 'MAINTENANCE_SUR_PLACE' || retourStep === 'POSTE_REMPLACE') && (
            <form onSubmit={submitRetour} className="space-y-4">
              {retourStep === 'POSTE_REMPLACE' && (
                <div>
                  <label className="field-label">{t('incidents.retourForm.newEquipement')}</label>
                  <select
                    className="input"
                    value={retourEquipementId}
                    onChange={(e) => setRetourEquipementId(e.target.value)}
                    required
                  >
                    <option value="" disabled>— Choisir un poste —</option>
                    {(equipementsDispo?.data ?? []).map((eq) => (
                      <option key={eq.id} value={eq.id}>{eq.nom}</option>
                    ))}
                  </select>
                  {equipementsDispo?.data?.length === 0 && (
                    <p className="mt-1 text-xs text-[var(--color-faint)]">Aucun poste disponible du même type sur ce site.</p>
                  )}
                </div>
              )}

              <div>
                <label className="field-label">{t('incidents.retourForm.commentaire')}</label>
                <textarea
                  className="input"
                  rows={3}
                  value={retourCommentaire}
                  onChange={(e) => setRetourCommentaire(e.target.value)}
                />
              </div>

              {retourError && <p className="text-xs" style={{ color: '#ff8983' }}>{retourError}</p>}

              <div className="flex justify-between gap-2">
                <button type="button" className="btn" onClick={() => setRetourStep('pick')}>{t('incidents.retourForm.back')}</button>
                <button type="submit" className="btn btn-primary" disabled={traiterRetour.isPending}>
                  {traiterRetour.isPending ? t('incidents.retourForm.submitting') : t('incidents.retourForm.submit')}
                </button>
              </div>
            </form>
          )}
        </div>
      </Modal>

      <Modal open={!!assigning} onClose={() => setAssigning(null)} title="Assigner à un technicien" width={420}>
        <form onSubmit={submitAssigner} className="space-y-4">
          <p className="text-sm text-[var(--color-muted)]">{assigning?.reference} — {assigning?.titre}</p>
          <div>
            <label className="field-label">Technicien</label>
            <select
              className="input"
              value={technicienId}
              onChange={(e) => setTechnicienId(e.target.value)}
              required
            >
             <option value="" disabled>— Choisir un technicien —</option>
{techniciensOptions.map((tech) => (
  <option key={tech.id} value={tech.id}>{tech.nomComplet}</option>
))}
            </select>
            {techniciensOptions.length === 1 && techniciensOptions[0].id === assigning?.employeId && (
              <p className="mt-1 text-xs text-[var(--color-faint)]">
                Aucun autre technicien disponible sur ce site : le technicien concerné devra traiter son propre incident.
              </p>
            )}
          </div>
          <div className="flex justify-end gap-2">
            <button type="button" className="btn" onClick={() => setAssigning(null)}>Annuler</button>
            <button type="submit" className="btn btn-primary" disabled={assigner.isPending || !technicienId}>
              {assigner.isPending ? 'Assignation...' : 'Assigner'}
            </button>
          </div>
        </form>
      </Modal>
    </>
  )
}

function IncidentDetailModal({ incident, onClose, locale }: { incident: Incident; onClose: () => void; locale: string }) {
 const { t } = useTranslation()
  const { user } = useAuth()
  const { data: commentaires } = useIncidentCommentaires(incident.id)
  const ajouterCommentaire = useAjouterCommentaire()
 const [message, setMessage] = useState('')

  async function submitCommentaire(e: React.FormEvent) {
    e.preventDefault()
    if (!message.trim()) return
    await ajouterCommentaire.mutateAsync({ id: incident.id, contenu: message })
    setMessage('')
  }

  return (
    <Modal open onClose={onClose} title={`${incident.reference} — ${incident.titre}`} width={520}>
      <div className="space-y-4 text-sm">
        <div className="flex flex-wrap gap-2">
          <StatusPill value={incident.statut} label={incident.statutLabel} />
          <StatusPill value={incident.priorite} label={incident.prioriteLabel} />
        </div>

        <div>
          <p className="field-label mb-1">Description</p>
          <p className="text-[var(--color-muted)]">{incident.description}</p>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <p className="field-label mb-1">Équipement</p>
            <p className="mono text-[var(--color-muted)]">{incident.equipement?.nom ?? '—'}</p>
          </div>
          <div>
            <p className="field-label mb-1">Date de signalement</p>
            <p className="mono text-[11px] text-[var(--color-faint)]">
              {new Date(incident.dateSignalement).toLocaleString(locale)}
            </p>
          </div>
          <div>
            <p className="field-label mb-1">Signalé par</p>
            <p className="text-[var(--color-muted)]">{incident.signalePar ?? '—'}</p>
          </div>
          <div>
            <p className="field-label mb-1">Traité par</p>
            <p className="text-[var(--color-muted)]">{incident.traitePar ?? '—'}</p>
          </div>
        </div>

{incident.dateRestitutionPrevue ? (
          <div>
            <p className="field-label mb-1">Action du technicien</p>
            <p className="text-[var(--color-muted)]">
              {incident.dateReceptionPoste
                ? `Votre poste sera prêt et vous sera rendu le ${new Date(incident.dateRestitutionPrevue).toLocaleString(locale)}.`
                : `Merci de ramener le poste au technicien le ${new Date(incident.dateRestitutionPrevue).toLocaleString(locale)}.`}
            </p>
          </div>
        ) : null}

        {incident.pieceJointes && incident.pieceJointes.length > 0 ? (
          <div>
            <p className="field-label mb-1">Pièces jointes</p>
            <ul className="space-y-1">
             {incident.pieceJointes.map((p, i) => (
                <li key={i}><a href={p.url} target="_blank" rel="noreferrer" className="text-sm underline" style={{ color: 'var(--color-brand)' }}>{p.nom}</a></li>
              ))}
            </ul>
          </div>
        ) : null}
        {incident.solution ? (
          <div>
            <p className="field-label mb-1">Solution apportée</p>
            <p className="text-[var(--color-muted)]">{incident.solution}</p>
            {incident.dateResolution ? (
              <p className="mono mt-1 text-[11px] text-[var(--color-faint)]">
                Résolu le {new Date(incident.dateResolution).toLocaleString(locale)}
              </p>
            ) : null}
          </div>
        ) : null}

        <div className="border-t border-[var(--color-line)] pt-3">
          <p className="field-label mb-2">{t('incidents.discussion.title')}</p>
          <div className="max-h-56 space-y-2 overflow-y-auto">
            {commentaires?.length ? (
              commentaires.map((c) => (
                <div key={c.id} className={`rounded-md p-2 text-[13px] ${c.auteurId === user?.id ? 'ml-8 bg-[var(--color-brand)]/10' : 'mr-8 bg-[var(--color-line)]/40'}`}>
                  <p className="mb-0.5 text-[11px] font-medium text-[var(--color-muted)]">{c.auteur ?? '—'}</p>
                  <p>{c.contenu}</p>
                  <p className="mono mt-1 text-[10px] text-[var(--color-faint)]">
                    {new Date(c.createdAt).toLocaleString(locale)}
                  </p>
                </div>
              ))
            ) : (
              <p className="text-[13px] text-[var(--color-faint)]">{t('incidents.discussion.empty')}</p>
            )}
          </div>
          <form onSubmit={submitCommentaire} className="mt-2 flex gap-2">
            <input
              className="input flex-1"
              placeholder={t('incidents.discussion.placeholder')}
              value={message}
              onChange={(e) => setMessage(e.target.value)}
            />
            <button type="submit" className="btn btn-primary px-3" disabled={ajouterCommentaire.isPending || !message.trim()}>
              {ajouterCommentaire.isPending ? t('incidents.discussion.sending') : t('incidents.discussion.send')}
            </button>
          </form>
        </div>

       <div className="flex justify-end pt-2">
          <button type="button" className="btn" onClick={onClose}>Fermer</button>
        </div>
      </div>
    </Modal>
  )
}