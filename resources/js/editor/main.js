import '../../css/editor/editor.css'
import { editorState } from './state'
import { renderEditor } from './editor/EditorShell'

const app = document.getElementById('editor-app')

if (!app) {
  throw new Error('Missing root #editor-app container')
}

renderEditor(app, editorState)
