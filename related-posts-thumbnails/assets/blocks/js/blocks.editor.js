/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__i18n_js__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__i18n_js___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__i18n_js__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__related_posts__ = __webpack_require__(2);


/**
 * Import blocks
 */

/***/ }),
/* 1 */
/***/ (function(module, exports) {

wp.i18n.setLocaleData({ '': {} }, 'related-post-thumbnails');

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__style_scss__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__style_scss___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__style_scss__);
/**
 * Block dependencies
 */


/**
 * Internal block libraries
 */
var __ = wp.i18n.__;
var registerBlockType = wp.blocks.registerBlockType;
var Spinner = wp.components.Spinner;
var withSelect = wp.data.withSelect;
var _wp$editor = wp.editor,
    RichText = _wp$editor.RichText,
    AlignmentToolbar = _wp$editor.AlignmentToolbar,
    BlockControls = _wp$editor.BlockControls,
    BlockAlignmentToolbar = _wp$editor.BlockAlignmentToolbar,
    InspectorControls = _wp$editor.InspectorControls;
var _wp$components = wp.components,
    Toolbar = _wp$components.Toolbar,
    Button = _wp$components.Button,
    Tooltip = _wp$components.Tooltip,
    PanelBody = _wp$components.PanelBody,
    PanelRow = _wp$components.PanelRow,
    FormToggle = _wp$components.FormToggle,
    TextControl = _wp$components.TextControl,
    SelectControl = _wp$components.SelectControl,
    ServerSideRender = _wp$components.ServerSideRender;


registerBlockType('related-post-thumbnails/rpt-block', {
  title: __('Related Posts', 'related-post-thumbnails'),
  description: __('A related posts block', 'related-post-thumbnails'),
  icon: 'grid-view',
  category: 'related-post-thumbnails-blocks',
  attributes: {
    mainTitle: {
      type: 'string'
    },
    postNumber: {
      type: 'string',
      default: 3
    },
    postSort: {
      type: 'string',
      default: 'random'
    }
  },
  edit: function edit(props) {
    var attributes = props.attributes,
        className = props.className,
        isSelected = props.isSelected,
        setAttributes = props.setAttributes;


    var changePostNumber = function changePostNumber(e) {
      if (e < 26) {
        props.setAttributes({ postNumber: e });
      }
    };

    var representBlocks = function representBlocks() {
      var table = [];

      // Outer loop to create parent
      for (var i = 0; i < props.attributes.postNumber; i++) {
        var children = [];
        //Inner loop to create children
        for (var j = 0; j < 5; j++) {
          children.push(wp.element.createElement(
            'td',
            null,
            'Column ' + (j + 1)
          ));
        }

        //Create the parent and add the children
        table.push(wp.element.createElement(
          'div',
          { className: 'rpt-single' },
          wp.element.createElement('div', { className: 'rpt-img' }),
          wp.element.createElement(
            'div',
            { className: 'rpt-text' },
            __('Post Title', 'related-post-thumbnails')
          )
        ));
      }
      return table;
    };

    return [wp.element.createElement(
      InspectorControls,
      null,
      wp.element.createElement(
        PanelBody,
        { title: __('Related Posts', 'related-post-thumbnails') },
        wp.element.createElement(
          PanelRow,
          null,
          wp.element.createElement(
            'strong',
            null,
            __('Related Posts Block Title', 'related-post-thumbnails')
          ),
          wp.element.createElement(TextControl, {
            value: props.attributes.mainTitle,
            onChange: function onChange(mainTitle) {
              return props.setAttributes({ mainTitle: mainTitle });
            }
          })
        ),
        wp.element.createElement(
          PanelRow,
          null,
          wp.element.createElement(
            'strong',
            null,
            __('Number of Posts', 'related-post-thumbnails')
          ),
          wp.element.createElement(TextControl, {
            type: 'number',
            value: props.attributes.postNumber,
            onChange: changePostNumber
          })
        ),
        wp.element.createElement(
          PanelRow,
          null,
          wp.element.createElement(
            'strong',
            null,
            __('Sort Posts Order', 'related-post-thumbnails')
          ),
          wp.element.createElement(SelectControl, {
            value: props.attributes.postSort,
            options: [{ value: "random", label: "Random" }, { value: "latest", label: "Latest" }],
            onChange: function onChange(postSort) {
              return props.setAttributes({ postSort: postSort });
            }
          })
        )
      )
    ), wp.element.createElement(
      'p',
      { className: 'rpt-represntaion-msg' },
      __('This is the representaion of Related Post Thumbnails', 'related-post-thumbnails')
    ), wp.element.createElement(
      'div',
      null,
      wp.element.createElement(
        'p',
        null,
        props.attributes.mainTitle
      ),
      wp.element.createElement(
        'div',
        { className: 'rpt-container' },
        representBlocks()
      )
    )];
  },
  save: function save() {
    // Rendering in PHP
    return null;
  }
});

/***/ }),
/* 3 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
/******/ ]);
