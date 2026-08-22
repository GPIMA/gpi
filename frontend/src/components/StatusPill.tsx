import type { CSSProperties } from 'react'

// Maps domain enum values to a signal colour. The label text always comes from
// the API (localized), so only the colour mapping lives here.
const SIGNAL: Record<string, string> = {
  // EtatEquipement
  EN_LIGNE: 'var(--color-online)',
  MAINTENANCE: 'var(--color-warn)',
  EN_PANNE: 'var(--color-down)',
  HORS_LIGNE: 'var(--color-idle)',
  // Severite / EtatAlerte / StatutIncident reuse the same scale
  FAIBLE: 'var(--color-idle)',
  MOYENNE: 'var(--color-info)',
  ELEVEE: 'var(--color-warn)',
  CRITIQUE: 'var(--color-down)',
  ACTIVE: 'var(--color-down)',
  EN_COURS: 'var(--color-warn)',
  RESOLUE: 'var(--color-online)',
  OUVERT: 'var(--color-down)',
  EN_MAINTENANCE: 'var(--color-warn)',
  RESOLU: 'var(--color-online)',
  FERME: 'var(--color-idle)',
  // RoleUtilisateur — utilisé pour signaler qu'un incident concerne un
  // membre du staff plutôt qu'un simple employé.
  TECHNICIEN: 'var(--color-brand-dim)',
  ADMIN: 'var(--color-brand-dim)',
  SUPER_ADMIN: 'var(--color-brand-dim)',
  // StatutDemandeChangementEtat / DemandeInscription
  EN_ATTENTE: 'var(--color-warn)',
  APPROUVEE: 'var(--color-online)',
  REJETEE: 'var(--color-down)',
}

export function StatusPill({ value, label }: { value: string; label: string }) {
  const color = SIGNAL[value] ?? 'var(--color-idle)'
  return (
    <span className="status" style={{ '--status-color': color } as CSSProperties}>
      {label}
    </span>
  )
}

// Returns the signal colour for a domain value — used to drive the severity
// rule on alert/incident rows.
export function signalColor(value: string): string {
  return SIGNAL[value] ?? 'var(--color-idle)'
}
