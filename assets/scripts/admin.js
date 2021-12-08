// Formality admin script
import { welcomePanel, newsletterForm } from './admin/main'
import { initExport } from './admin/export'

document.addEventListener('DOMContentLoaded', () => {
  welcomePanel()
  newsletterForm()
  initExport()
})
