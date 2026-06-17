import type { IconName } from '@/components/icons'
import type { Role } from '@/lib/api/types'

export interface NavItem {
  to: string
  /** i18n key under nav.* */
  key: string
  icon: IconName
  /** Roles allowed to see this entry; empty = everyone authenticated. */
  roles?: Role[]
}

export interface NavSection {
  /** i18n key under nav.sections.* */
  titleKey: string
  items: NavItem[]
}

// Navigation mirrors the use-case diagram, grouped and gated by role.
export const NAV_SECTIONS: NavSection[] = [
  {
    titleKey: 'exploitation',
    items: [
      { to: '/dashboard', key: 'dashboard', icon: 'dashboard' },
      { to: '/equipements', key: 'equipements', icon: 'equipements' },
      { to: '/supervision', key: 'supervision', icon: 'supervision' },
      { to: '/alertes', key: 'alertes', icon: 'alertes' },
      { to: '/incidents', key: 'incidents', icon: 'incidents' },
    ],
  },
  {
    titleKey: 'intelligence',
    items: [
      { to: '/predictions', key: 'predictions', icon: 'predictions' },
      { to: '/assistant', key: 'chatbot', icon: 'chatbot' },
    ],
  },
  {
    titleKey: 'systeme',
    items: [
      { to: '/regles', key: 'regles', icon: 'regles', roles: ['ADMIN'] },
      { to: '/administration', key: 'administration', icon: 'administration', roles: ['ADMIN'] },
    ],
  },
]

export function visibleSections(role: Role): NavSection[] {
  return NAV_SECTIONS.map((section) => ({
    ...section,
    items: section.items.filter((i) => !i.roles || i.roles.includes(role)),
  })).filter((section) => section.items.length > 0)
}
