// Formality admin script
import { welcomePanel, newsletterForm } from './modules/main'
import { initExport } from './modules/export'

document.addEventListener('DOMContentLoaded', () => {
  welcomePanel()
  newsletterForm()
  initExport()
})
