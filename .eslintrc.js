module.exports = {
  "root": true,
  "extends": [
    "eslint:recommended",
    'plugin:react/recommended'
  ],
  "globals": {
    "wp": true,
    "loadash": {},
    "formality": {}
  },
  "env": {
    "node": true,
    "es6": true,
    "amd": true,
    "browser": true,
    "jquery": true
  },
  "parserOptions": {
    "ecmaFeatures": {
      "globalReturn": true,
      "generators": false,
      "objectLiteralDuplicateProperties": false,
      "experimentalObjectRestSpread": true,
      "jsx": true,
      "modules": true,
    },
    "ecmaVersion": 2017,
    "sourceType": "module"
  },
  "plugins": [
    "import"
  ],
  "settings": {
    "import/core-modules": [],
    "import/ignore": [
      "node_modules",
      "\\.(coffee|scss|css|less|hbs|svg|json)$"
    ]
  },
  "rules": {
    "react/jsx-key": 0,
    "no-console": process.env.NODE_ENV === 'production' ? 2 : 0,
    "comma-dangle": [
      "error",
      {
        "arrays": "always-multiline",
        "objects": "always-multiline",
        "imports": "always-multiline",
        "exports": "always-multiline",
        "functions": "ignore"
      }
    ]
  }
}
