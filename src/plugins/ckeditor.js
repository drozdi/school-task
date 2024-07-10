/**
 * @license Copyright (c) 2014-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor.js';
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment.js';
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat.js';
import AutoLink from '@ckeditor/ckeditor5-link/src/autolink.js';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold.js';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials.js';
import GeneralHtmlSupport from '@ckeditor/ckeditor5-html-support/src/generalhtmlsupport.js';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic.js';
import Link from '@ckeditor/ckeditor5-link/src/link.js';
import List from '@ckeditor/ckeditor5-list/src/list.js';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph.js';
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice.js';
import SourceEditing from '@ckeditor/ckeditor5-source-editing/src/sourceediting.js';
import SpecialCharacters from '@ckeditor/ckeditor5-special-characters/src/specialcharacters.js';
import Subscript from '@ckeditor/ckeditor5-basic-styles/src/subscript.js';
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript.js';
import Table from '@ckeditor/ckeditor5-table/src/table.js';
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar.js';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline.js';

import '@ckeditor/ckeditor5-alignment/build/translations/ru';
import '@ckeditor/ckeditor5-link/build/translations/ru';
import '@ckeditor/ckeditor5-list/build/translations/ru';
import '@ckeditor/ckeditor5-basic-styles/build/translations/ru';
import '@ckeditor/ckeditor5-html-support/build/translations/ru';
import '@ckeditor/ckeditor5-source-editing/build/translations/ru';
import '@ckeditor/ckeditor5-special-characters/build/translations/ru';
import '@ckeditor/ckeditor5-table/build/translations/ru';
import '@ckeditor/ckeditor5-build-classic/build/translations/ru';

class Editor extends ClassicEditor {}

// Plugins to include in the build.
Editor.builtinPlugins = [
	Alignment,
	Autoformat,
	AutoLink,
	Bold,
	Essentials,
	GeneralHtmlSupport,
	Italic,
	Link,
	List,
	Paragraph,
	PasteFromOffice,
	SourceEditing,
	SpecialCharacters,
	Subscript,
	Superscript,
	Table,
	TableToolbar,
	Underline
];

// Editor configuration.
Editor.defaultConfig = {
	toolbar: {
		items: [
			'undo',
			'redo',
			'bold',
			'italic',
			'underline',
			'subscript',
			'superscript',
			'|',
			'numberedList',
			'bulletedList',
			'alignment',
			'|',
			'link',
			'|',
			'insertTable',
			'specialCharacters',
			'sourceEditing'
		],
		shouldNotGroupWhenFull: true
	},
	//language: 'ru',
	table: {
		contentToolbar: [
			'tableColumn',
			'tableRow',
			'mergeTableCells'
		]
	}
};

export default Editor;
