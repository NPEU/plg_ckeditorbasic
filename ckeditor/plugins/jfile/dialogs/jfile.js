/**
 * @package     jfile
 *
 * @copyright   Copyright (C) NPEU 2020.
 * @license     MIT License; see LICENSE.md
 */

'use strict';

CKEDITOR.dialog.add('jfileDialog', function(editor) {

    var thumb_alt = 'Thumbnail preview of the file.';

    var mimes = {
        'doc'  : 'application/msword',
        'docx' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pdf'  : 'application/pdf',
        'ppt'  : 'application/vnd.ms-powerpoint',
        'pptx' : 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xls'  : 'application/vnd.ms-excel',
        'xlsx' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'zip'  : 'application/zip'
    }

    return {
        title: 'File',
        minWidth: 400,
        minHeight: 200,

        jfile: {},
        contents: [
            {
                id: 'tab--settings',
                label: 'Settings',
                elements: [
                    {
                        id: 'href',
                        type: 'html',
                        html: '<div class="controls"><div id="field-media-wrapper" class="field-media-wrapper" data-basepath="' + window.location.origin + '/" data-url="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=com_menus&amp;author=&amp;fieldid={field-media-id}&amp;ismoo=0&amp;folder=downloads/" data-modal=".modal" data-modal-width="100%" data-modal-height="645px" data-input=".field-media-input" data-button-select=".button-select" data-button-clear=".button-clear" data-button-save-selected=".button-save-selected" data-preview="false" data-preview-as-tooltip="false" data-preview-container=".field-media-preview" data-preview-width="200" data-preview-height="200"><div id="imageModal_jfile_href" tabindex="-1" class="modal hide fade"><div class="modal-header"><button type="button" class="close novalidate" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button><h3>Change File</h3></div><div class="modal-body"></div></div><div class="input-append"><input type="text" name="jfile_href" id="jfile_href" value="" readonly="readonly" class="input-small hasTooltip field-media-input" data-original-title="" title=""><button type="button" class="btn button-select">Select</button><button type="button" class="btn hasTooltip button-clear" title="" aria-label="Clear" data-original-title="Clear"><span class="icon-remove" aria-hidden="true"></span></button></div></div></div>',
                        onLoad: function() {
                            var el = this;

                            jQuery(document).find('#field-media-wrapper').fieldMedia();

                            jQuery('#jfile_href').change(function() {
                                var href = this.value;
                                if (href != '') {
                                    el.setValue('/' + href);
                                }
                            });
                        },
                        onShow: function() {
                            // Update the url so that the correct folder is loaded into the
                            // modal for better UX for existing files:
                            var cur_val = this.getValue();
                            
                            if (cur_val == '') {
                                return;
                            }

                            var t = jQuery('#field-media-wrapper').attr('data-url') + cur_val.replace(/.*\/downloads\//, '');
                            var a = t.split('/');
                            a.pop();
                            t = a.join('/') + '/';

                            jQuery('#field-media-wrapper').data('fieldMedia').options.url = t;
                        },
                        setup: function(jfile_data) {
                            this.setValue(jfile_data.href);
                        }
                    },
                    {
                        id: 'text',
                        type: 'text',
                        label: 'Link text',
                        inputStyle: 'margin: 0',
                        'default': '',
                        validate: CKEDITOR.dialog.validate.notEmpty( 'The Text field cannot be empty.' ),
                        setup: function(jfile_data) {
                            this.setValue(jfile_data.text);
                        }
                    },
                    {
                        id: 'thumbSize',
                        type: 'select',
                        label: 'Thumbnail size',
                        items: [ [ 'None', 'none' ], [ 'Small', '150' ], [ 'Medium', '300' ], [ 'Large', '600' ] ],
                        controlStyle: 'width: 8em; display: inline-block; vertical-align: middle;',
                        inputStyle: 'margin: 0; vertical-align: middle',
                        labelStyle: 'vertical-align: middle; width: 10em',
                        'default': 'none',
                        setup: function(jfile_data) {
                            this.setValue(jfile_data.thumbSize);
                        }
                    },
                    {
                        id: 'thumbOnly',
                        type: 'checkbox',
                        label: 'Thumbnail only?',
                        inputStyle: 'margin: 0; vertical-align: middle',
                        labelStyle: 'vertical-align: middle; width: 10em',
                        setup: function(jfile_data) {
                            this.setValue(jfile_data.thumbOnly);
                        },
                        commit: function(jfile_data) {
                            jfile_data.thumbOnly = this.getValue();
                        }
                    }
                ]
            }
        ],

        onShow: function() {
            // Reset the jfile data:
            this.definition.jfile = {};

            this.insertMode = true;

            var dialog     = this;
            var definition = dialog.definition;
            var jfile_data = definition.jfile;

            var selection    = editor.getSelection();
            var element      = selection.getStartElement();

            console.log('Onshow element', element);

            if (element.getAttribute('data-cke-widget-wrapper') === '1') {
                // Widget selected, look for a jfile:
                var links            = element.find('a[href][type]');
                var jfile            = links.toArray()[0];
                var jfile_thumb      = element.findOne('img[src^="/"]');
                var jfile_thumb_only = element.getText() == '';
                //var jfile_thumb_only = (links.count() == 1 && jfile_thumb);

                //console.log('Onshow jfile_thumb', jfile_thumb);

                if (jfile) {
                    // We're in Edit mode:
                    this.insertMode = false;

                    jfile_data.href = jfile.getAttribute('href');

                    jfile_data.text = element.getText() == ''
                                    ? jfile_thumb.getAttribute('alt').replace('. ' + thumb_alt, '')
                                    : element.getText();

                    jfile_data.thumbSize = jfile_thumb
                                         ? jfile_thumb.getAttribute('width')
                                         : 'none';

                    jfile_data.thumbOnly = jfile_thumb_only;


                    jQuery('#jfile_href').attr('value', jfile_data.href);
                }
            }

            console.log('jfile_data', jfile_data);

            // Remove cke_reset_all
            jQuery('.cke_dialog_container').removeClass('cke_reset_all');

            if (!this.insertMode) {
                this.setupContent(jfile_data);
            }
        },

        onHide: function() {
            // Clean up:
            this.definition.jfile = {};
            // Make sure the joomla href is cleared:
            jQuery('#jfile_href').val('');
            this.setValueOf('tab--settings', 'href', '');

        },

        onOk: function() {
            var dialog = this;
            var jfile_href = dialog.getValueOf('tab--settings', 'href');

            // Check we have an href - can't continue without that.
            if (typeof(jfile_href) == 'undefined' || jfile_href == '') {
                return;
            }

            var jfile_text      = CKEDITOR.tools.trim(dialog.getValueOf('tab--settings', 'text'));
            var jfile_thumbsize = CKEDITOR.tools.trim(dialog.getValueOf('tab--settings', 'thumbSize'));
            var jfile_thumbonly = dialog.getValueOf('tab--settings', 'thumbOnly');

            if (jfile_href != '') {
                var data_modified_time = '';

                var file_info = JSON.parse(CKEDITOR.ajax.load(jfile_href + '.json'));
                //console.log('INFO', file_info);

                if (file_info) {
                    if (typeof file_info.modified_time !== 'undefined') {
                        data_modified_time = ' data-modified-time="' + file_info.modified_time + '"';
                    }
                }

                var jfile_html = [];

                jfile_html.push('<span data-contains="download"' + data_modified_time + '>');

                var type = mimes[jfile_href.split('.').pop()];
                var a = '<a href="' + jfile_href + '" type="' + type + '"';

                if (jfile_thumbsize == 'none') {
                    // No thumbnail:
                    jfile_html.push(a + '>');
                    jfile_html.push(jfile_text);
                    jfile_html.push('</a>');
                } else {
                    jfile_html.push(a + ' data-contains="thumbnail">');

                    var alt = jfile_thumbonly
                            ? jfile_text + '. '
                            : '';
                    jfile_html.push('<img alt="' + alt + thumb_alt + '" src="' + jfile_href + '.png?s=' + jfile_thumbsize + '" width="' + jfile_thumbsize + '">');

                    if (!jfile_thumbonly) {
                        jfile_html.push('<br>');
                        jfile_html.push('<span>' + jfile_text + '</span>');
                    }
                }
                jfile_html.push('</a>');
                jfile_html.push('</span>');

                editor.insertHtml(CKEDITOR.tools.trim(jfile_html.join("")));
            } else {
                editor.insertHtml('');
            }
        }
    };
});
