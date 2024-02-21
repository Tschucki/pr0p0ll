module.exports = {
    extends: [
        'eslint:recommended',
        'plugin:vue/vue3-recommended',
        'prettier'
    ],
    plugins: ['prettier'],
    rules: {
        'vue/no-unused-properties': ['error'],
        'vue/no-v-html': 'off',
        'vue/multi-word-component-names': 'off',
        'prettier/prettier': ['error'],
        'vue/require-default-prop': 'off',
        'vue/singleline-html-element-content-newline': 0,
        'vue/component-name-in-template-casing': ['error', 'PascalCase'],
    },
}
