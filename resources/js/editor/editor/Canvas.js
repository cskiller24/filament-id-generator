import Konva from 'konva/lib/index.js'
import { centeredDropPosition } from '../lib/canvasMath'
import { urlToImageElement } from '../lib/imageLoader'

const DEFAULT_TEXT_STYLE = {
  fontFamily: 'Manrope',
  textColor: '#0f172a',
  hasBackground: false,
  backgroundColor: '#ffffff',
}

const TEXT_PADDING_X = 14
const TEXT_PADDING_Y = 10

function readTextStyle(node) {
  const style = node.getAttr('textStyle')
  if (!style || typeof style !== 'object') {
    return { ...DEFAULT_TEXT_STYLE }
  }

  return { ...DEFAULT_TEXT_STYLE, ...style }
}

function layoutTextGroup(group) {
  const refs = group.getAttr('textRefs')
  if (!refs) {
    return
  }

  const { background, label } = refs
  const style = readTextStyle(group)

  const boxWidth = Math.max(40, label.width() + TEXT_PADDING_X * 2)
  const boxHeight = Math.max(32, label.height() + TEXT_PADDING_Y * 2)

  background.size({ width: boxWidth, height: boxHeight })
  background.visible(style.hasBackground)
  background.fill(style.backgroundColor)
  background.stroke(style.hasBackground ? '#dbe2ea' : 'transparent')
  background.shadowOpacity(style.hasBackground ? 0.24 : 0)

  label.position({ x: TEXT_PADDING_X, y: TEXT_PADDING_Y })

  group.width(boxWidth)
  group.height(boxHeight)
}

function applyTextStyleToNode(node, patch) {
  if (node.getAttr('editableKind') !== 'text') {
    return null
  }

  const refs = node.getAttr('textRefs')
  if (!refs) {
    return null
  }

  const style = {
    ...readTextStyle(node),
    ...patch,
  }

  refs.label.fontFamily(style.fontFamily)
  refs.label.fill(style.textColor)

  node.setAttr('textStyle', style)
  layoutTextGroup(node)

  return style
}

function buildTextGroup(text, textStyle = null) {
  const initialStyle = textStyle ? { ...DEFAULT_TEXT_STYLE, ...textStyle } : { ...DEFAULT_TEXT_STYLE }

  const label = new Konva.Text({
    x: TEXT_PADDING_X,
    y: TEXT_PADDING_Y,
    text,
    fontSize: 24,
    fontFamily: initialStyle.fontFamily,
    fill: initialStyle.textColor,
  })

  const card = new Konva.Rect({
    width: 40,
    height: 32,
    fill: initialStyle.backgroundColor,
    cornerRadius: 10,
    stroke: 'transparent',
    strokeWidth: 1,
    shadowColor: 'rgba(15, 23, 42, 0.24)',
    shadowBlur: 12,
    shadowOffset: { x: 0, y: 4 },
    visible: false,
  })

  const group = new Konva.Group({ draggable: true, width: 40, height: 32 })
  group.setAttr('editableKind', 'text')
  group.setAttr('textStyle', initialStyle)
  group.setAttr('textRefs', {
    background: card,
    label,
  })

  group.add(card, label)
  layoutTextGroup(group)

  return group
}

function buildImagePlaceholder(fieldLabel) {
  const width = 180
  const height = 120

  const shell = new Konva.Rect({
    width,
    height,
    fill: '#f1f5f9',
    cornerRadius: 12,
    stroke: '#94a3b8',
    dash: [6, 5],
    strokeWidth: 1.2,
  })

  const text = new Konva.Text({
    x: 14,
    y: 48,
    width: width - 28,
    text: fieldLabel,
    fontSize: 18,
    align: 'center',
    fill: '#475569',
    fontFamily: 'Manrope',
  })

  const group = new Konva.Group({ draggable: true, width, height })
  group.add(shell, text)
  return group
}

function getNodeSize(node) {
  const rect = node.getClientRect()
  return {
    width: rect.width,
    height: rect.height,
  }
}

export async function mountCanvasWithOptions(container, options = {}) {
  const {
    stageWidth,
    stageHeight,
    previewUrl,
    fields,
    sampleValues,
    initialPlacements,
    onSelectionChange = () => {},
  } = options

  const stage = new Konva.Stage({
    container,
    width: stageWidth,
    height: stageHeight,
  })

  const backgroundLayer = new Konva.Layer()
  const elementsLayer = new Konva.Layer()
  const transformerLayer = new Konva.Layer()

  if (previewUrl) {
    try {
      const bgImage = await urlToImageElement(previewUrl)
      backgroundLayer.add(
        new Konva.Image({
          image: bgImage,
          width: stageWidth,
          height: stageHeight,
          listening: false,
          isBackground: true,
        })
      )
    } catch {
      // Fallback to plain background below.
    }
  }

  if (backgroundLayer.children.length === 0) {
    backgroundLayer.add(
      new Konva.Rect({
        width: stageWidth,
        height: stageHeight,
        fill: '#f5f5f4',
        listening: false,
        isBackground: true,
      })
    )
  }

  const transformer = new Konva.Transformer({
    rotateEnabled: true,
    enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
    boundBoxFunc: (oldBox, newBox) => {
      if (newBox.width < 28 || newBox.height < 28) {
        return oldBox
      }

      return newBox
    },
  })

  transformerLayer.add(transformer)
  stage.add(backgroundLayer)
  stage.add(elementsLayer)
  stage.add(transformerLayer)

  stage.on('click tap', (event) => {
    if (event.target === stage || event.target?.getAttr?.('isBackground')) {
      transformer.nodes([])
      transformerLayer.batchDraw()
      onSelectionChange(null)
    }
  })

  const placements = []

  const syncPlacedRecord = (node, id, mappingKey) => {
    const size = getNodeSize(node)
    const existing = placements.find((placement) => placement.id === id)
    const textStyle = node.getAttr('editableKind') === 'text' ? readTextStyle(node) : undefined

    const record = {
      id,
      mappingKey,
      x: Math.round(node.x()),
      y: Math.round(node.y()),
      width: Math.round(size.width),
      height: Math.round(size.height),
      rotation: node.rotation(),
      textStyle,
    }

    if (existing) {
      Object.assign(existing, record)
      return
    }

    placements.push(record)
  }

  const attachEditable = (node, mappingKey) => {
    const id = crypto.randomUUID()

    node.setAttr('placementId', id)
    node.setAttr('placementMappingKey', mappingKey)

    node.on('click tap', (event) => {
      event.cancelBubble = true
      transformer.nodes([node])
      transformerLayer.batchDraw()

      if (node.getAttr('editableKind') === 'text') {
        onSelectionChange({
          kind: 'text',
          style: readTextStyle(node),
        })
      } else {
        onSelectionChange(null)
      }
    })

    node.on('dragend', () => {
      syncPlacedRecord(node, id, mappingKey)
    })

    node.on('transformend', () => {
      syncPlacedRecord(node, id, mappingKey)
    })

    syncPlacedRecord(node, id, mappingKey)
  }

  for (const placement of initialPlacements ?? []) {
    const field = fields[placement.mapping_key]

    if (!field) {
      continue
    }

    let node

    if (field.type === 'image') {
      node = buildImagePlaceholder(field.label)
    } else {
      const textStyle = placement.properties?.textStyle ?? null
      const text = sampleValues[placement.mapping_key] || field.label
      node = buildTextGroup(text, textStyle)
    }

    node.position({
      x: placement.x,
      y: placement.y,
    })

    const baseWidth = node.width() || 1
    const baseHeight = node.height() || 1
    const targetWidth = placement.width || baseWidth
    const targetHeight = placement.height || baseHeight

    node.scale({
      x: targetWidth / baseWidth,
      y: targetHeight / baseHeight,
    })

    if (placement.properties?.rotation) {
      node.rotation(placement.properties.rotation)
    }

    if (field.type === 'text' && placement.properties?.textStyle) {
      applyTextStyleToNode(node, placement.properties.textStyle)
    }

    attachEditable(node, placement.mapping_key)
    elementsLayer.add(node)
  }

  elementsLayer.batchDraw()

  const handleDrop = async ({ dropX, dropY, mappingKey }) => {
    const field = fields[mappingKey]

    if (!field) {
      return
    }

    let node

    if (field.type === 'image') {
      node = buildImagePlaceholder(field.label)
    } else {
      const text = sampleValues[mappingKey] || field.label
      node = buildTextGroup(text)
    }

    const size = getNodeSize(node)
    const pos = centeredDropPosition({
      dropX,
      dropY,
      nodeWidth: size.width,
      nodeHeight: size.height,
      stageWidth,
      stageHeight,
    })

    node.position(pos)
    attachEditable(node, mappingKey)
    elementsLayer.add(node)
    elementsLayer.batchDraw()
  }

  const setSelectedTextStyle = (patch) => {
    const selectedNode = transformer.nodes()[0]

    if (!selectedNode || selectedNode.getAttr('editableKind') !== 'text') {
      return false
    }

    const nextStyle = applyTextStyleToNode(selectedNode, patch)

    if (!nextStyle) {
      return false
    }

    const placementId = selectedNode.getAttr('placementId')
    const mappingKey = selectedNode.getAttr('placementMappingKey')

    syncPlacedRecord(selectedNode, placementId, mappingKey)

    elementsLayer.batchDraw()
    transformerLayer.batchDraw()

    onSelectionChange({
      kind: 'text',
      style: nextStyle,
    })

    return true
  }

  const collectLayout = () => {
    return placements.map((placement, index) => {
      const field = fields[placement.mappingKey]

      return {
        label: field?.label ?? placement.mappingKey,
        type: field?.type ?? 'text',
        mapping_key: placement.mappingKey,
        x: placement.x,
        y: placement.y,
        width: placement.width,
        height: placement.height,
        z_index: index,
        is_required: false,
        default_value: null,
        properties: {
          rotation: placement.rotation ?? 0,
          ...(placement.textStyle ? { textStyle: placement.textStyle } : {}),
        },
      }
    })
  }

  return {
    stage,
    handleDrop,
    setSelectedTextStyle,
    collectLayout,
    destroy() {
      onSelectionChange(null)
      stage.destroy()
    },
  }
}
