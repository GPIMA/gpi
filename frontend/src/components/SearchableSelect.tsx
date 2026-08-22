import { useEffect, useRef, useState } from 'react'
import { Icons } from './icons'

export interface SearchableSelectOption {
  value: string
  label: string
}

/**
 * Combobox à une seule case : un champ texte qui sert à la fois de
 * recherche et d'affichage de la valeur choisie, avec une liste filtrée qui
 * s'ouvre en dessous. Remplace le duo « champ de recherche + <select> ».
 */
export function SearchableSelect({
  options,
  value,
  onChange,
  placeholder,
  emptyLabel = 'Aucun résultat',
  disabled,
}: {
  options: SearchableSelectOption[]
  value: string
  onChange: (value: string) => void
  placeholder?: string
  emptyLabel?: string
  disabled?: boolean
}) {
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState('')
  const containerRef = useRef<HTMLDivElement>(null)

  const selected = options.find((o) => o.value === value)

  useEffect(() => {
    function onClickOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false)
        setQuery('')
      }
    }
    document.addEventListener('mousedown', onClickOutside)
    return () => document.removeEventListener('mousedown', onClickOutside)
  }, [])

  const filtered = options.filter((o) => o.label.toLowerCase().includes(query.trim().toLowerCase()))

  function pick(v: string) {
    onChange(v)
    setOpen(false)
    setQuery('')
  }

  return (
    <div className="relative" ref={containerRef}>
      <div className="relative">
        <span className="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--color-faint)]">
          <Icons.search size={14} />
        </span>
        <input
          className="input pl-8"
          placeholder={placeholder}
          value={open ? query : (selected?.label ?? '')}
          onFocus={() => {
            setOpen(true)
            setQuery('')
          }}
          onChange={(e) => setQuery(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter') e.preventDefault()
            if (e.key === 'Escape') { setOpen(false); setQuery('') }
          }}
          disabled={disabled}
          autoComplete="off"
        />
      </div>
      {open && (
        <div
          className="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-md border shadow-lg"
          style={{ borderColor: 'var(--color-line)', background: 'var(--color-surface)' }}
        >
          {value && (
            <button
              type="button"
              className="block w-full px-3 py-2 text-left text-sm text-[var(--color-faint)] hover:bg-[var(--color-brand-wash)]"
              onClick={() => pick('')}
            >
              —
            </button>
          )}
          {filtered.length === 0 && (
            <div className="px-3 py-2 text-sm text-[var(--color-faint)]">{emptyLabel}</div>
          )}
          {filtered.map((o) => (
            <button
              type="button"
              key={o.value}
              className="block w-full px-3 py-2 text-left text-sm hover:bg-[var(--color-brand-wash)]"
              onClick={() => pick(o.value)}
            >
              {o.label}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
