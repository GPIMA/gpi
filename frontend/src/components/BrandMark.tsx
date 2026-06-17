/**
 * GPI mark — unified with the public vitrine palette.
 * Light professional mark used across login, dashboard and app shell.
 */
export function BrandMark({ size = 28 }: { size?: number }) {
  const C = 2 * Math.PI * 11

  return (
    <svg width={size} height={size} viewBox="0 0 40 40" fill="none" aria-hidden>
      <rect x="1" y="1" width="38" height="38" rx="12" fill="#ffffff" stroke="#d8e5ee" />
      <circle
        cx="20"
        cy="20"
        r="11"
        fill="none"
        stroke="#073b67"
        strokeWidth="3.4"
        strokeLinecap="round"
        strokeDasharray={`${C * 0.82} ${C * 0.18}`}
        strokeDashoffset={C * 0.09}
      />
      <path d="M20 20 H29" stroke="#65aedc" strokeWidth="3.4" strokeLinecap="round" />
      <circle cx="20" cy="20" r="2.1" fill="#65aedc" />
      <circle cx="30" cy="10" r="3" fill="#d6df00" />
    </svg>
  )
}
