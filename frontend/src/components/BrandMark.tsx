/**
 * Power GPI mark — a lightning bolt built from connected network nodes:
 * "Power" (the bolt) meets IT fleet management (the connected nodes).
 * Same palette (navy → cyan gradient, lime accent) as the rest of the brand.
 * Light professional mark used across login, dashboard and app shell.
 */
export function BrandMark({ size = 28 }: { size?: number }) {
  return (
    <svg width={size} height={size} viewBox="0 0 40 40" fill="none" aria-hidden>
      <defs>
        <linearGradient id="brandBolt" x1="10" y1="4" x2="30" y2="36">
          <stop stopColor="#073b67" />
          <stop offset="1" stopColor="#00b4e8" />
        </linearGradient>
      </defs>
      <rect x="1" y="1" width="38" height="38" rx="12" fill="#ffffff" stroke="#d8e5ee" />
      <path d="M23 4 L10 22 H17 L15 36 L30 16 H21 Z" fill="url(#brandBolt)" />
      <circle cx="23" cy="4" r="2.4" fill="#073b67" />
      <circle cx="30" cy="16" r="2.4" fill="#00b4e8" />
      <circle cx="15" cy="36" r="2.4" fill="#65aedc" />
      <circle cx="32" cy="9" r="3" fill="#d6df00" />
    </svg>
  )
}
