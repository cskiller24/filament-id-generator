export function renderToolbar(templateName) {
  return `
    <header class="editor-toolbar panel-glass animate-rise" data-ui="editor-toolbar">
      <div class="toolbar-copy">
        <h2>${templateName}</h2>
        <p>Drag fields into the canvas, then move, resize, or rotate elements.</p>
      </div>
      <div class="toolbar-actions">
        <button type="button" class="btn-muted" data-action="back-step">&larr; Back to Template</button>
        <button type="button" class="btn-accent" data-action="save-layout">Save Layout</button>
      </div>
    </header>
  `
}
