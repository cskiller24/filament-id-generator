const bootstrap = window.__EDITOR_BOOTSTRAP__

export const editorState = {
  templateId: bootstrap.templateId,
  templateName: bootstrap.templateName,
  adapterFields: bootstrap.adapterFields,
  sides: bootstrap.sides,
  sampleValues: bootstrap.sampleValues,
  saveUrl: bootstrap.saveUrl,
  backUrl: bootstrap.backUrl,
  layouts: { ...bootstrap.initialLayouts },
}
