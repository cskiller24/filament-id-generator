import { renderFieldList } from './FieldList'
import { renderToolbar } from './toolbar'
import { mountCanvasWithOptions } from './Canvas'

function renderShell(container, { state, activeSideKey }) {
  const tabsHtml = state.sides
    .map(
      (side) =>
        `<button type="button" class="side-tab ${side.key === activeSideKey ? 'is-active' : ''}" data-action="switch-side" data-side="${side.key}">${side.label}</button>`
    )
    .join('')

  container.innerHTML = `
    <section class="editor-atmosphere">
      <div data-ui="editor-grid" class="editor-grid">
        <aside data-ui="left-panel" class="left-rail panel-glass animate-rise">
          <h2 class="m-0 text-2xl">Fields</h2>
          <p class="helper-copy">Drag any chip into the canvas as many times as you need.</p>
          <div data-ui="field-rail" class="rail-chips"></div>

          <section class="text-style-panel panel-glass" data-ui="text-style-panel">
            <h3 class="text-style-title">Text Style</h3>

            <label class="style-label" for="font-family-select">Font</label>
            <select id="font-family-select" class="style-control" data-style="font-family" disabled>
              <option value="Manrope">Manrope</option>
              <option value="Fraunces">Fraunces</option>
              <option value="Georgia">Georgia</option>
              <option value="Times New Roman">Times New Roman</option>
              <option value="Courier New">Courier New</option>
            </select>

            <div class="style-grid">
              <label class="style-label" for="text-color-input">Text color</label>
              <input id="text-color-input" type="color" class="style-control" data-style="text-color" value="#0f172a" disabled />
            </div>

            <label class="style-checkbox" for="bg-enabled-input">
              <input id="bg-enabled-input" type="checkbox" data-style="bg-enabled" disabled />
              Enable text background
            </label>

            <div class="style-grid">
              <label class="style-label" for="bg-color-input">Background color</label>
              <input id="bg-color-input" type="color" class="style-control" data-style="bg-color" value="#ffffff" disabled />
            </div>

            <p class="helper-copy" data-ui="text-style-hint">Select a text element on the canvas to style it.</p>
          </section>
        </aside>

        <main data-ui="canvas-panel" class="canvas-frame panel-glass animate-rise">
          ${renderToolbar(state.templateName)}

          <div class="editor-toolbar" style="padding-top: 0; margin-bottom: 0.5rem;">
            <div class="side-tabs" data-ui="side-tabs">${tabsHtml}</div>
          </div>

          <section data-ui="canvas-scroll" class="canvas-scroll">
            <div data-ui="canvas-host" class="canvas-host"></div>
          </section>
        </main>
      </div>
    </section>
  `
}

function showToast(message, isSuccess = true) {
  const toast = document.createElement('div')
  toast.className = `save-toast ${isSuccess ? 'is-success' : 'is-error'}`
  toast.textContent = message
  document.body.append(toast)

  setTimeout(() => toast.remove(), 3000)
}

export async function renderEditor(container, state) {
  if (!Array.isArray(state.sides) || state.sides.length === 0) {
    container.innerHTML = `
      <section class="editor-atmosphere">
        <div class="panel-glass" style="max-width: 960px; margin: 0 auto; padding: 1rem;">
          <h2 style="margin-top: 0;">No template sides found</h2>
          <p>Upload at least a front source image/PDF, save the template, then return here to edit layout.</p>
          <a class="btn-muted" style="display: inline-block; text-decoration: none;" href="${state.backUrl}">Back to Template</a>
        </div>
      </section>
    `
    return
  }

  let activeSideKey = state.sides[0]?.key ?? 'front'
  let canvasController = null
  let currentCleanup = null

  const mountCanvas = async () => {
    const activeSide = state.sides.find((entry) => entry.key === activeSideKey)

    if (!activeSide) {
      return
    }

    const canvasHost = container.querySelector('[data-ui="canvas-host"]')
    const canvasScroll = container.querySelector('[data-ui="canvas-scroll"]')
    const fontFamilyControl = container.querySelector('[data-style="font-family"]')
    const textColorControl = container.querySelector('[data-style="text-color"]')
    const bgEnabledControl = container.querySelector('[data-style="bg-enabled"]')
    const bgColorControl = container.querySelector('[data-style="bg-color"]')
    const styleHint = container.querySelector('[data-ui="text-style-hint"]')

    let selectedStyle = null

    const syncStyleControls = (selection) => {
      selectedStyle = selection?.kind === 'text' ? selection.style : null

      const disabled = !selectedStyle
      fontFamilyControl.disabled = disabled
      textColorControl.disabled = disabled
      bgEnabledControl.disabled = disabled

      if (!selectedStyle) {
        fontFamilyControl.value = 'Manrope'
        textColorControl.value = '#0f172a'
        bgEnabledControl.checked = false
        bgColorControl.value = '#ffffff'
        bgColorControl.disabled = true
        styleHint.textContent = 'Select a text element on the canvas to style it.'
        return
      }

      fontFamilyControl.value = selectedStyle.fontFamily
      textColorControl.value = selectedStyle.textColor
      bgEnabledControl.checked = Boolean(selectedStyle.hasBackground)
      bgColorControl.value = selectedStyle.backgroundColor
      bgColorControl.disabled = !selectedStyle.hasBackground
      styleHint.textContent = 'Styling is applied to the selected text element.'
    }

    canvasController = await mountCanvasWithOptions(canvasHost, {
      stageWidth: activeSide.canvas_width,
      stageHeight: activeSide.canvas_height,
      previewUrl: activeSide.preview_url,
      fields: state.adapterFields,
      sampleValues: state.sampleValues,
      initialPlacements: state.layouts[activeSideKey] ?? [],
      onSelectionChange: syncStyleControls,
    })

    const applyStylePatch = () => {
      if (!selectedStyle) {
        return
      }

      canvasController.setSelectedTextStyle({
        fontFamily: fontFamilyControl.value,
        textColor: textColorControl.value,
        hasBackground: bgEnabledControl.checked,
        backgroundColor: bgColorControl.value,
      })

      state.layouts[activeSideKey] = canvasController.collectLayout()
    }

    const handleDragOver = (event) => {
      event.preventDefault()

      if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'copy'
      }
    }

    const handleDrop = async (event) => {
      event.preventDefault()

      const mappingKey = event.dataTransfer?.getData('text/field-name') || event.dataTransfer?.getData('text/plain')

      if (!mappingKey) {
        return
      }

      const hostRect = canvasHost.getBoundingClientRect()
      const dropX = event.clientX - hostRect.left + canvasScroll.scrollLeft
      const dropY = event.clientY - hostRect.top + canvasScroll.scrollTop

      await canvasController.handleDrop({ dropX, dropY, mappingKey })
      state.layouts[activeSideKey] = canvasController.collectLayout()
    }

    const handleFontFamilyChange = () => {
      applyStylePatch()
    }

    const handleTextColorChange = () => {
      applyStylePatch()
    }

    const handleBackgroundEnabledChange = () => {
      bgColorControl.disabled = !bgEnabledControl.checked
      applyStylePatch()
    }

    const handleBackgroundColorChange = () => {
      applyStylePatch()
    }

    syncStyleControls(null)

    canvasScroll.addEventListener('dragover', handleDragOver)
    canvasScroll.addEventListener('drop', handleDrop)
    fontFamilyControl.addEventListener('change', handleFontFamilyChange)
    textColorControl.addEventListener('input', handleTextColorChange)
    bgEnabledControl.addEventListener('change', handleBackgroundEnabledChange)
    bgColorControl.addEventListener('input', handleBackgroundColorChange)

    currentCleanup = () => {
      canvasScroll.removeEventListener('dragover', handleDragOver)
      canvasScroll.removeEventListener('drop', handleDrop)
      fontFamilyControl.removeEventListener('change', handleFontFamilyChange)
      textColorControl.removeEventListener('input', handleTextColorChange)
      bgEnabledControl.removeEventListener('change', handleBackgroundEnabledChange)
      bgColorControl.removeEventListener('input', handleBackgroundColorChange)

      canvasController.destroy()
      canvasController = null
    }
  }

  const wireShellListeners = () => {
    const saveButton = container.querySelector('[data-action="save-layout"]')
    const backButton = container.querySelector('[data-action="back-step"]')
    const sideTabs = container.querySelectorAll('[data-action="switch-side"]')
    const rail = container.querySelector('[data-ui="field-rail"]')

    saveButton?.addEventListener('click', async () => {
      if (canvasController) {
        state.layouts[activeSideKey] = canvasController.collectLayout()
      }

      saveButton.disabled = true
      saveButton.textContent = 'Saving...'

      try {
        const response = await fetch(state.saveUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
          },
          body: JSON.stringify({
            side: activeSideKey,
            fields: state.layouts[activeSideKey] ?? [],
          }),
        })

        if (!response.ok) {
          throw new Error(`Save failed with status ${response.status}`)
        }

        showToast('Layout saved', true)
      } catch (error) {
        console.error(error)
        showToast('Save failed - please try again', false)
      } finally {
        saveButton.disabled = false
        saveButton.textContent = 'Save Layout'
      }
    })

    backButton?.addEventListener('click', () => {
      window.location.href = state.backUrl
    })

    sideTabs.forEach((tab) => {
      tab.addEventListener('click', async () => {
        const nextSideKey = tab.dataset.side

        if (!nextSideKey || nextSideKey === activeSideKey) {
          return
        }

        if (canvasController) {
          state.layouts[activeSideKey] = canvasController.collectLayout()
        }

        currentCleanup?.()
        currentCleanup = null

        activeSideKey = nextSideKey
        renderShell(container, { state, activeSideKey })
        wireShellListeners()
        await mountCanvas()
      })
    })

    if (rail) {
      renderFieldList(rail, state.adapterFields)
    }
  }

  renderShell(container, { state, activeSideKey })
  wireShellListeners()
  await mountCanvas()
}
