import App from './App.svelte'

window.addEventListener('load', () => {
  
  const root = document.createElement('div')
  root.id = 'bulk-delete'

  document.body.appendChild(root)

  const app = new App({
    target: root,
  })

})