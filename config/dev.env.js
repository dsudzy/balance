'use strict'
const merge = require('webpack-merge')
const prodEnv = require('./prod.env')

module.exports = merge(prodEnv, {
  NODE_ENV: '"development"',
  VUE_APP_APP_PORT: 8000,
  VUE_APP_PLAID_CLIENT_ID: '"5b28fcfe5666c40012691852"',
  VUE_APP_PLAID_SECRET: '"6c399b9075ac92c43963497f7752a4"',
  VUE_APP_PLAID_PUBLIC_KEY: '"3fd48ca30df117a9ed536aa76bfb50"',
  VUE_APP_PLAID_ENV: '"sandbox"'
})
