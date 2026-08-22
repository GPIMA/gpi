// Shapes returned by the Laravel API. Domain option lists are never hardcoded
// here — they arrive at runtime through the /enums endpoint (see EnumOption).

export type Role = 'SUPER_ADMIN' | 'ADMIN' | 'TECHNICIEN' | 'EMPLOYE'

export interface Utilisateur {
  id: number
  nom: string
  prenom: string
  nomComplet: string
  email: string
  telephone: string | null
  role: Role
  roleLabel: string
  departement: string | null
  localisation: string | null
  posteActuel?: { id: number; nom: string; type: string | null; typeLabel: string | null } | null
  dateCreation: string
}

export interface LoginResponse {
  token: string
  utilisateur: Utilisateur
}

export interface EnumOption {
  value: string
  label: string
}

export interface Equipement {
  id: number
  nom: string
  type: string
  typeLabel: string
  marque: string | null
  modele: string | null
  numeroSerie: string | null
  adresseIP: string | null
  adresseMAC: string | null
  etat: string
  etatLabel: string
  localisation: string | null
  dateAcquisition: string | null
  affectation?: { id: number; employeId: number; employe: string } | null
  demandeChangementEtatEnAttente?: {
    id: number
    etatActuel: string
    etatActuelLabel: string
    etatDemande: string
    etatDemandeLabel: string
    createdAt: string
  } | null
  dateCreation: string
}

export interface DemandeChangementEtatCommentaire {
  id: number
  contenu: string
  auteur: string | null
  auteurId: number
  createdAt: string
}

export interface DemandeChangementEtat {
  id: number
  equipement?: { id: number; nom: string; type: string; typeLabel: string; localisation: string | null }
  demandeur?: { id: number; nomComplet: string }
  etatActuel: string
  etatActuelLabel: string
  etatDemande: string
  etatDemandeLabel: string
  statut: 'EN_ATTENTE' | 'APPROUVEE' | 'REJETEE'
  statutLabel: string
  motif: string | null
  traitePar?: string | null
  traiteLe: string | null
  commentaireTraitement: string | null
  createdAt: string
}

/** Laravel paginated resource collection. */
export interface Paginated<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
}

export interface Alerte {
  id: number
  type: string
  typeLabel: string
  severite: string
  severiteLabel: string
  message: string
  etat: string
  etatLabel: string
  dateCreation: string
  dateResolution: string | null
  equipement?: { id: number; nom: string }
  regle?: string | null
}

export interface RegleAlerte {
  id: number
  nom: string
  metriqueCible: string
  operateur: string
  seuil: number
  severite: string
  severiteLabel: string
  typeAlerte: string
  typeAlerteLabel: string
  actif: boolean
}

export interface Incident {
  id: number
  reference: string
  titre: string
  description: string
  statut: string
  statutLabel: string
  priorite: string
  prioriteLabel: string
  solution: string | null
  dateSignalement: string
  dateResolution: string | null
  dateRestitutionPrevue?: string | null
  /** Poste déjà reçu par le technicien : distingue "à ramener" de "à récupérer" pour dateRestitutionPrevue. */
  dateReceptionPoste?: string | null
  pieceJointes?: { url: string; nom: string }[]
  equipement?: { id: number; nom: string; type?: string; localisation?: string | null }
employeId?: number
signalePar?: string
signaleParRole?: string
signaleParRoleLabel?: string
traitePar?: string | null
}
export interface IncidentCommentaire {
  id: number
  contenu: string
  auteur: string | null
  auteurId: number
  createdAt: string
}
export interface NotificationItem {
  id: number
  sujet: string
  contenu: string
  canal: string
  canalLabel: string
  statut: string
  lue: boolean
  dateEnvoi: string
}

export interface Prediction {
  id: number
  typePanne: string
  typePanneLabel: string
  probabilite: number
  horizonJours: number
  dateGeneration: string
  equipement?: { id: number; nom: string }
  modele?: { nom: string; algorithme: string; version: string }
}

export interface ModeleIA {
  nom: string
  algorithme: string
  version: string
  precision: number
  dateEntrainement: string | null
}

export interface ChatMessage {
  id: number
  contenu: string
  expediteur: string
  estChatbot: boolean
  dateEnvoi: string
}

export interface Conversation {
  id: number
  titre: string | null
  dateDebut: string
  dateFin: string | null
  messages?: ChatMessage[]
  dernierMessage?: string | null
}

export interface DashboardData {
  parc: {
    total: number
    parEtat: { etat: string; label: string; total: number }[]
  }
  alertes: {
    actives: number
    parSeverite: Record<string, number>
    recentes: Alerte[]
  }
}

export interface EnumDictionary {
  typeEquipement: EnumOption[]
  etatEquipement: EnumOption[]
  typeAlerte: EnumOption[]
  severite: EnumOption[]
  etatAlerte: EnumOption[]
  canalNotification: EnumOption[]
  statutIncident: EnumOption[]
  motifRetourPoste: EnumOption[]
  expediteurType: EnumOption[]
  roleUtilisateur: EnumOption[]
  statutDemandeChangementEtat: EnumOption[]
}
export interface DemandeInscription {
  id: number
  nom: string
  prenom: string
  email: string
  role: string
  telephone: string | null
  departement: string | null
  message: string | null
  statut: 'EN_ATTENTE' | 'APPROUVEE' | 'REJETEE'
  created_at: string
}