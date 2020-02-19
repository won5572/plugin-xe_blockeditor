import { addQueryArgs } from './add-query-args'
import apiFetch from '../api/api-fetch'

window.userSettings = {
  secure: '',
  time: 1234567,
  uid: 1
}

// set your root path
window.wpApiSettings = {
  root: window.location.origin + '/',
  nonce: '1234567890',
  versionString: 'wp/v2'
}

// postboxes
window.postboxes = window.postboxes || {
  add_postbox_toggles: (page, args) => {
    //
  }
}

window.wp.url = { ...window.wp.url, addQueryArgs }

const {
  use,
  createRootURLMiddleware,
  setFetchHandler
} = window.wp.apiFetch

use(createRootURLMiddleware(window.wpApiSettings.root))
setFetchHandler(apiFetch)

// Some editor settings
export const editorSettings = {
  target: 'xeContentEditor',
  alignWide: true,
  availableTemplates: [],
  allowedBlockTypes: true,
  disableCustomColors: false,
  disablePostFormats: false,
  mediaLibrary: true,
  titlePlaceholder: 'Add title',
  bodyPlaceholder: 'Write your story',
  isRTL: false,
  maxWidth: 1044,
  hasPermissionsToManageWidgets: true,
  postLock: {
    isLocked: false
  },
  formats: {
    date: 'YYYY-MM-DD',
    time: 'HH:mm:ss'
  }
}

// Post properties to override
export const overridePost = {}
