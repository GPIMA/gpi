// Minimal stroke icons drawn inline — keeps the chrome consistent and avoids a
// generic icon-library footprint. 24px grid, 1.6 stroke.

type IconProps = { size?: number; className?: string }

function svg(path: React.ReactNode) {
  return function Icon({ size = 18, className }: IconProps) {
    return (
      <svg
        width={size}
        height={size}
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.6"
        strokeLinecap="round"
        strokeLinejoin="round"
        className={className}
      >
        {path}
      </svg>
    )
  }
}

export const Icons = {
  dashboard: svg(<>
    <rect x="3" y="3" width="7" height="9" rx="1" />
    <rect x="14" y="3" width="7" height="5" rx="1" />
    <rect x="14" y="12" width="7" height="9" rx="1" />
    <rect x="3" y="16" width="7" height="5" rx="1" />
  </>),
  equipements: svg(<>
    <rect x="3" y="4" width="18" height="12" rx="1.5" />
    <path d="M8 20h8M12 16v4" />
  </>),
  alertes: svg(<>
    <path d="M12 4l9 16H3z" />
    <path d="M12 10v4M12 17.5v.5" />
  </>),
  regles: svg(<>
    <path d="M4 6h16M4 12h10M4 18h16" />
    <circle cx="17" cy="12" r="2" />
  </>),
  incidents: svg(<>
    <circle cx="12" cy="12" r="8.5" />
    <path d="M12 8v5M12 16v.5" />
  </>),
  predictions: svg(<>
    <path d="M5 19V9M12 19V5M19 19v-7" />
    <path d="M5 9l7-4 7 7" opacity="0.5" />
  </>),
  chatbot: svg(<>
    <rect x="3" y="5" width="18" height="12" rx="2.5" />
    <path d="M8 21l3-4M16 21l-3-4M9 11h.01M15 11h.01" />
  </>),
  administration: svg(<>
    <circle cx="9" cy="8" r="3" />
    <path d="M3 20a6 6 0 0 1 12 0M17 11l1.5 1.5L22 9" />
  </>),
  logout: svg(<>
    <path d="M14 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-2" />
    <path d="M18 12H9M15 9l3 3-3 3" />
  </>),
  search: svg(<>
    <circle cx="11" cy="11" r="7" />
    <path d="M21 21l-4-4" />
  </>),
  close: svg(<path d="M6 6l12 12M18 6L6 18" />),
  plus: svg(<path d="M12 5v14M5 12h14" />),
  edit: svg(<>
    <path d="M4 20h4L19 9l-4-4L4 16z" />
    <path d="M14 6l4 4" />
  </>),
  trash: svg(<>
    <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13" />
  </>),
  history: svg(<>
    <circle cx="12" cy="12" r="8.5" />
    <path d="M12 7v5l3.5 2" />
  </>),
  chevronLeft: svg(<path d="M15 6l-6 6 6 6" />),
  chevronRight: svg(<path d="M9 6l6 6-6 6" />),
  bell: svg(<>
    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
    <path d="M13.7 21a2 2 0 0 1-3.4 0" />
  </>),
  check: svg(<path d="M5 12l5 5L20 6" />),
  send: svg(<path d="M4 12l16-7-7 16-2-7-7-2z" />),
  assign: svg(<>
    <circle cx="9" cy="8" r="3.2" />
    <path d="M3.5 20a5.7 5.7 0 0 1 11 0" />
    <path d="M16 11l2 2 3.5-3.5" />
  </>),
}

export type IconName = keyof typeof Icons
