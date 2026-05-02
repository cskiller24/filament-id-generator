const DPI = 96
const MM_PER_INCH = 25.4

export function mmToPx(mm) {
  return (Number(mm) * DPI) / MM_PER_INCH
}

export function clamp(value, min, max) {
  return Math.min(Math.max(value, min), max)
}

export function centeredDropPosition({
  dropX,
  dropY,
  nodeWidth,
  nodeHeight,
  stageWidth,
  stageHeight,
}) {
  const rawX = dropX - nodeWidth / 2
  const rawY = dropY - nodeHeight / 2

  return {
    x: clamp(rawX, 0, Math.max(0, stageWidth - nodeWidth)),
    y: clamp(rawY, 0, Math.max(0, stageHeight - nodeHeight)),
  }
}
