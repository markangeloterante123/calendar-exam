const middleware = {}

middleware['trailingSlashRedirect'] = require('../middleware/trailingSlashRedirect.js')
middleware['trailingSlashRedirect'] = middleware['trailingSlashRedirect'].default || middleware['trailingSlashRedirect']

export default middleware
