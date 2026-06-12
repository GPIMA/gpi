// Shapes returned by the Laravel API. Domain option lists are never hardcoded
// here — they arrive at runtime through the /enums endpoint (see EnumOption).

export type Role = 'ADMIN' | 'TECHNICIEN' | 'EMPLOYE'

export interface Utilisateur {
  id: number
  nom: string
  prenom: string
  nomComplet: string
  email: string
  telephone: string | null
  role: Role
  roleLabel: string
  specialite: string | null
  departement: string | null
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
  adresseIP: string | null
  adresseMAC: string | null
  etat: string
  etatLabel: string
  localisation: string | null
  dateAcquisition: string | null
  affectation?: { id: number; employe: string } | null
  dateCreation: string
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

export interface Metrique {
  id: number
  dateHeure: string
  cpu: number
  ram: number
  disque: number
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
  titre: string
  description: string
  statut: string
  statutLabel: string
  priorite: string
  prioriteLabel: string
  solution: string | null
  dateSignalement: string
  dateResolution: string | null
  equipement?: { id: number; nom: string }
  signalePar?: string
  traitePar?: string | null
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

export interface SupervisionRow {
  equipement: Equipement
  metrique: Metrique | null
}

export interface EnumDictionary {
  typeEquipement: EnumOption[]
  etatEquipement: EnumOption[]
  typeAlerte: EnumOption[]
  severite: EnumOption[]
  etatAlerte: EnumOption[]
  canalNotification: EnumOption[]
  statutIncident: EnumOption[]
  expediteurType: EnumOption[]
  roleUtilisateur: EnumOption[]
}
