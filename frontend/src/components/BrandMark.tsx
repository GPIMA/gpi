/**
 * GPI mark — a "G" drawn as a monitoring gauge: an open ring (the letter G,
 * and a dial) with a cyan signal tongue and indicator. Geometric, single-weight,
 * scales cleanly from favicon to hero.
 */
export function BrandMark({ size = 28 }: { size?: number }) {
  // Ring geometry (viewBox 40). Gap on the east side forms the G's opening.
  const C = 2 * Math.PI * 11 // circumference ≈ 69.12

  return (
    <svg width={size} height={size} viewBox="0 0 40 40" fill="none" aria-hidden>
      <rect x="1" y="1" width="38" height="38" rx="4" fill="#11151b" stroke="#2d3742" />
      {/* Gauge ring with an opening at 3 o'clock */}
      <circle
        cx="20"
        cy="20"
        r="11"
        fill="none"
        stroke="#ededee"
        strokeWidth="3.4"
        strokeLinecap="round"
        strokeDasharray={`${C * 0.82} ${C * 0.18}`}
        strokeDashoffset={C * 0.09}
      />
      {/* The G's tongue / dial needle — signal cyan */}
      <path d="M20 20 H29" stroke="#4cc2d6" strokeWidth="3.4" strokeLinecap="round" />
      {/* Signal indicator */}
      <circle cx="20" cy="20" r="2.1" fill="#4cc2d6" />
    </svg>
  )
}
