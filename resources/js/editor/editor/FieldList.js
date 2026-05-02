export function renderFieldList(container, adapterFields) {
  container.innerHTML = ''

  Object.entries(adapterFields).forEach(([key, field], index) => {
    const chip = document.createElement('button')
    chip.type = 'button'
    chip.className = 'field-chip animate-rise'
    chip.setAttribute('data-ui', 'field-chip')
    chip.setAttribute('draggable', 'true')
    chip.dataset.fieldName = key
    chip.dataset.fieldType = field.type
    chip.dataset.fieldLabel = field.label
    chip.style.animationDelay = `${Math.min(index * 45, 220)}ms`

    const label = document.createElement('span')
    label.textContent = field.label

    const typeBadge = document.createElement('small')
    typeBadge.className = 'chip-type'
    typeBadge.textContent = field.type

    chip.append(label, typeBadge)

    chip.addEventListener('dragstart', (event) => {
      if (!event.dataTransfer) {
        return
      }

      event.dataTransfer.effectAllowed = 'copy'
      event.dataTransfer.setData('text/field-name', key)
      event.dataTransfer.setData('text/plain', key)
    })

    container.append(chip)
  })
}
