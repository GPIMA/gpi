import { Component, type ReactNode } from 'react'

interface Props {
  children: ReactNode
}

interface State {
  error: Error | null
}

/**
 * Catches render errors in a page so a single failure shows a message instead
 * of blanking the whole application.
 */
export class ErrorBoundary extends Component<Props, State> {
  state: State = { error: null }

  static getDerivedStateFromError(error: Error): State {
    return { error }
  }

  reset = () => this.setState({ error: null })

  render() {
    if (this.state.error) {
      return (
        <div className="panel m-6 p-8 text-center">
          <p className="eyebrow mb-2">Erreur</p>
          <p className="mb-1 text-lg">Une erreur est survenue dans ce module.</p>
          <p className="mono mb-6 text-xs text-[var(--color-faint)]">{this.state.error.message}</p>
          <button className="btn btn-primary" onClick={this.reset}>Réessayer</button>
        </div>
      )
    }
    return this.props.children
  }
}
