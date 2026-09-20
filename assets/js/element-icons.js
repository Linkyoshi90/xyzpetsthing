(function (global) {
  'use strict';

  // Element glyphs are keyed by element_id (see the `elements` table), never by name -
  // the display names have been renamed a few times (Norm -> Vulgaris, Malus -> Venom, ...)
  // and the ids are the only stable handle.
  // Every glyph draws inside a 24x24 box and inherits the surrounding text colour.
  var ELEMENTS = {
    1: {
      name: 'Vulgaris',
      color: '#b9c6d6',
      glyph: '<path fill-rule="evenodd" d="M12 3.6a8.4 8.4 0 1 0 0 16.8 8.4 8.4 0 0 0 0-16.8zm0 3.6a4.8 4.8 0 1 1 0 9.6 4.8 4.8 0 0 1 0-9.6z"/>'
    },
    2: {
      name: 'Heat',
      color: '#ff8a3d',
      glyph: '<path d="M12 2c3.4 3.9 5.3 6.9 5.3 9.6a5.3 5.3 0 1 1-10.6 0c0-1.9.8-3.8 2.4-5.6.1 1.5.7 2.5 1.7 3.1C11.5 6.9 11.7 4.6 12 2z"/>'
    },
    3: {
      name: 'Vai',
      color: '#4cc9f0',
      glyph: '<path d="M12 2.2c4.2 5.2 6.6 8.4 6.6 11.3a6.6 6.6 0 1 1-13.2 0C5.4 10.6 7.8 7.4 12 2.2z"/>'
    },
    4: {
      name: 'Electra',
      color: '#ffd633',
      glyph: '<path d="M13.8 2 6 13.3h4.7L9.4 22 18 10.5h-4.9L13.8 2z"/>'
    },
    5: {
      name: 'Flora',
      color: '#63d471',
      glyph: '<path d="M20.4 3.2C10.8 3.2 5 7.3 5 13.3c0 1.9.6 3.5 1.7 4.8 2.2-5.3 5.4-8.6 10-10.6-3.9 2.7-6.6 6.3-8 11.4 1 .4 2 .6 3.1.6 6.9 0 9.4-6.6 8.6-16.3z"/><path d="M3.4 20.6c1-2.2 2.2-4 3.6-5.5l1.2 1.1c-1.2 1.3-2.3 3-3.2 4.9l-1.6-.5z"/>'
    },
    6: {
      name: 'Air',
      color: '#bde0fe',
      glyph: '<path d="M2.8 6.6h9.4a1.5 1.5 0 1 0-1.4-2l-2.1-.7A3.7 3.7 0 1 1 12.2 8.8H2.8V6.6z"/><rect x="2.8" y="10.9" width="13.4" height="2.2" rx="1.1"/><path d="M2.8 15.2h6.9a3.7 3.7 0 1 1 3.5 4.9l.7-2.1a1.5 1.5 0 1 0-1.4-2H2.8v-.8z"/>'
    },
    7: {
      name: 'Ground',
      color: '#d2a679',
      glyph: '<path d="M3 20.4h18v-2.6H3v2.6z"/><path d="M4.7 16.4 8 11.9h2.8l1.9-2.6 1.9 2.6H17l3.3 4.5H4.7z"/><path d="M11.2 9.1 12 3.6l.8 5.5h-1.6z"/>'
    },
    8: {
      name: 'Stone',
      color: '#b8a58f',
      glyph: '<path d="m12 3 8.2 5.6-3 11.2H6.8l-3-11.2L12 3z"/>'
    },
    9: {
      name: 'Psy',
      color: '#d6a4ff',
      glyph: '<path fill-rule="evenodd" d="M12 5.2c5.4 0 9.4 6.4 9.4 6.8s-4 6.8-9.4 6.8S2.6 12.4 2.6 12s4-6.8 9.4-6.8zm0 3.4a3.4 3.4 0 1 0 0 6.8 3.4 3.4 0 0 0 0-6.8z"/><circle cx="12" cy="12" r="1.6"/>'
    },
    10: {
      name: 'Arthropod',
      color: '#a3c65a',
      glyph: '<ellipse cx="12" cy="14.2" rx="4.6" ry="6.2"/><circle cx="12" cy="6.4" r="2.6"/><g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M10.6 4.2 9.2 2.4M13.4 4.2l1.4-1.8M7.4 10.6 3.6 9M7 14.4H3M7.4 18.2l-3.4 2M16.6 10.6 20.4 9M17 14.4h4M16.6 18.2l3.4 2"/></g>'
    },
    11: {
      name: 'Venom',
      color: '#a855f7',
      glyph: '<path d="M5 5.6a7 7 0 0 1 14 0c0 3-1.5 4.9-3.1 5.6l-1.7-4.4-1.4 5H11l-1.4-5-1.7 4.4C6.3 10.5 5 8.6 5 5.6z"/><path d="M12 13.9c2.1 2.7 3.2 4.3 3.2 5.5a3.2 3.2 0 1 1-6.4 0c0-1.2 1.1-2.8 3.2-5.5z"/>'
    },
    12: {
      name: 'Fight',
      color: '#f0894e',
      glyph: '<path d="M6.2 9.8c0-1 .8-1.9 1.9-1.9h.6V6.5a1.9 1.9 0 1 1 3.8 0v1.4h.7V5.9a1.9 1.9 0 1 1 3.8 0v2h.5c1 0 1.9.9 1.9 1.9v3.9a6.4 6.4 0 0 1-12.8 0V9.8z"/><path d="M6.2 11.4v3.1l-2-2a1.6 1.6 0 0 1 2-1.1z"/>'
    },
    13: {
      name: 'Cold',
      color: '#7de2ff',
      glyph: '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 2.6v18.8M4 7.3l16 9.4M20 7.3 4 16.7"/><path d="M9.4 4.7 12 6.3l2.6-1.6M9.4 19.3 12 17.7l2.6 1.6M3.9 11l.3-3 2.9-.7M20.1 13l-.3 3-2.9.7M6.9 16.7l-2.7.3-.3-3M17.1 7.3l2.7-.3.3 3"/></g>'
    },
    14: {
      name: 'Ethereal',
      color: '#b9a6ff',
      glyph: '<path fill-rule="evenodd" d="M12 2.4a7 7 0 0 1 7 7v12l-2.6-2-2.2 2-2.2-2-2.2 2-2.2-2-2.6 2v-12a7 7 0 0 1 7-7zm-2.4 6.2a1.4 1.4 0 1 0 0 2.8 1.4 1.4 0 0 0 0-2.8zm4.8 0a1.4 1.4 0 1 0 0 2.8 1.4 1.4 0 0 0 0-2.8z"/>'
    },
    15: {
      name: 'Dark',
      color: '#7c83a8',
      glyph: '<path d="M15.8 2.8A9.2 9.2 0 1 0 21.4 15 7.4 7.4 0 0 1 15.8 2.8z"/>'
    },
    16: {
      name: 'Iron',
      color: '#9fb3c8',
      glyph: '<path fill-rule="evenodd" d="M12 2.4 20.3 7v10L12 21.6 3.7 17V7L12 2.4zm0 5.7a3.9 3.9 0 1 0 0 7.8 3.9 3.9 0 0 0 0-7.8z"/>'
    },
    17: {
      name: 'Draco',
      color: '#7ab8ff',
      glyph: '<path d="M5.6 2.6c2.2 2.9 3.3 6.6 3.4 11.2l-2.4 6.9 3.2-5 .9 5.7 1.3-5.9 2.6 5-1-6.6c1.9-3 3.4-6.6 4.6-10.9-2.7 2.7-4.8 5.6-6.3 8.6-1.4-3.4-3.5-6.4-6.3-9z"/>'
    },
    18: {
      name: 'Fae',
      color: '#ff9ed2',
      glyph: '<path d="M11.4 1.8c.9 5.7 3.4 8.3 9 9.2-5.6.9-8.1 3.5-9 9.2-.9-5.7-3.4-8.3-9-9.2 5.6-.9 8.1-3.5 9-9.2z"/><path d="M18.4 15.4c.4 2.4 1.4 3.5 3.8 3.9-2.4.4-3.4 1.5-3.8 3.9-.4-2.4-1.4-3.5-3.8-3.9 2.4-.4 3.4-1.5 3.8-3.9z"/>'
    }
  };

  var FALLBACK = ELEMENTS[1];

  function entry(id) {
    return ELEMENTS[Number(id)] || FALLBACK;
  }

  function svg(id, size) {
    var data = entry(id);
    var px = Number(size) || 16;
    return '<svg class="element-icon" viewBox="0 0 24 24" width="' + px + '" height="' + px +
      '" aria-hidden="true" focusable="false" fill="currentColor">' + data.glyph + '</svg>';
  }

  // Full badge: coloured icon plus the element name, for move buttons and detail cards.
  function badge(id, name, size) {
    var data = entry(id);
    var label = name || data.name;
    return '<span class="element-badge" style="color:' + data.color + '" title="' + label + '">' +
      svg(id, size) + '<span class="element-badge-name">' + label + '</span></span>';
  }

  global.ElementIcons = {
    all: ELEMENTS,
    svg: svg,
    badge: badge,
    color: function (id) { return entry(id).color; },
    name: function (id) { return entry(id).name; }
  };
})(window);
