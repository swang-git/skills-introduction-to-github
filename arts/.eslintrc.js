module.exports = {
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-recommended',
    'plugin:@typescript-eslint/recommended'
  ],
  rules: {
    'vue/component-name-in-template-casing': ['error', 'PascalCase'],
    'no-console': ctx.dev ? 'warn' : 'error',
    'no-debugger': ctx.dev ? 'warn' : 'error'
  }
}
