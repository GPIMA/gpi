/**
 * Decorative infrastructure motif — a hand-laid network topology (nodes + links)
 * used as a low-opacity backdrop so empty areas read as "infrastructure" instead
 * of dead space. Pure SVG line-art, no imagery. Steel lines, a few cyan signal
 * nodes. Inert: aria-hidden + pointer-events-none at the call site.
 */

// Nodes on a 600×360 field. `hot` nodes get the cyan accent.
const NODES: { x: number; y: number; r: number; hot?: boolean }[] = [
  { x: 70, y: 60, r: 3 },
  { x: 180, y: 40, r: 4, hot: true },
  { x: 300, y: 90, r: 3 },
  { x: 430, y: 50, r: 4 },
  { x: 540, y: 110, r: 3, hot: true },
  { x: 110, y: 170, r: 4 },
  { x: 250, y: 200, r: 5, hot: true },
  { x: 390, y: 165, r: 3 },
  { x: 510, y: 220, r: 4 },
  { x: 60, y: 290, r: 3 },
  { x: 200, y: 320, r: 4 },
  { x: 340, y: 285, r: 3, hot: true },
  { x: 470, y: 320, r: 4 },
  { x: 570, y: 300, r: 3 },
]

// Links by node index.
const LINKS: [number, number][] = [
  [0, 1], [1, 2], [2, 3], [3, 4], [0, 5], [1, 6], [2, 6], [3, 7],
  [4, 8], [5, 6], [6, 7], [7, 8], [5, 9], [6, 10], [7, 11], [8, 12],
  [9, 10], [10, 11], [11, 12], [12, 13], [10, 6], [11, 7], [4, 7],
]

export function NetworkBackdrop({ className = '' }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 600 360"
      fill="none"
      aria-hidden
      className={className}
      preserveAspectRatio="xMidYMid meet"
    >
      <g stroke="var(--color-line-strong)" strokeWidth="1">
        {LINKS.map(([a, b], i) => (
          <line key={i} x1={NODES[a].x} y1={NODES[a].y} x2={NODES[b].x} y2={NODES[b].y} />
        ))}
      </g>
      {NODES.map((n, i) => (
        <circle
          key={i}
          cx={n.x}
          cy={n.y}
          r={n.r}
          fill={n.hot ? 'var(--color-brand)' : 'var(--color-faint)'}
          stroke={n.hot ? 'var(--color-brand)' : 'var(--color-line-strong)'}
          strokeWidth="1"
        />
      ))}
    </svg>
  )
}
