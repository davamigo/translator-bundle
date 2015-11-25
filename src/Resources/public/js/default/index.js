/**
 * @file bundles/davamigotranslator/js/default/index.js
 * @author davamigo@gmail.com
 */

(function (doc, $) {

    'use strict';

    var $table,
        $dataTable;

    /**
     * init()
     * @param {string} [selector]
     * @param {object} [context]
     */
    var init = function(selector, context) {

        if (!selector) {
            selector = '.js-table';
        }

        $table = $(selector, context);
        if (!$table.length) {
            throw 'Invalid selector: ' + selector;
        }

        // Create the data-table
        $dataTable = $table.DataTable({

            'columnDefs': [{
                'targets': '_all',
                'render': function (data, type, full, meta) {
                    if (!data) {
                        data = '&ndash;';
                    }
                    else {
                        data = data
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;');
                    }
                    return data;
                }
            }],

            'initComplete': function() {

                this.api().columns().every(function() {

                    var column = this,
                        search = column.search(),
                        $header = $(column.header()),
                        filter = $header.data('filter'),
                        locale = $header.data('locale'),
                        columnId = $header.data('column-id'),
                        $filterCell = $('.js-column-' + columnId, $table);

                    switch (filter) {

                        case 'select': {

                            var $select = $('<select class="form-control input-sm"><option value=""></option></select>')
                                .appendTo($filterCell.empty())
                                .on('change', function() {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                                });

                            column.data().unique().sort().each(function(data) {

                                var selected = '';
                                if (search && data.match(search)) {
                                    selected = ' selected="selected"';
                                }

                                $select.append('<option value="' + data + '"' + selected + '>' + data + '</option>')
                            });

                        } break;

                        case 'input': {

                            var $input = $('<input type="text" class="form-control input-sm" />')
                                .appendTo($filterCell.empty())
                                .on('keyup change', function() {
                                    column.search(this.value).draw();
                                });

                            $input.val(search);

                        } break;
                    }

                    if (locale) {
                        var $btn = $('a.js-btn-locale-toggle[data-locale="' + locale +'"]');
                        enableLocaleButton($btn, column.visible());
                    }
                });
            }
        });


        /**
         * Locale buttons
         */
        var $localeButtons = $('a.js-btn-locale-toggle');
        $localeButtons.unbind('click');
        $localeButtons.on('click', function (ev) {
            ev.preventDefault();

            var $btn = $(this),
                colNum = $btn.data('col-num'),
                column = $dataTable.column(colNum);

            column.visible(!column.visible());
            enableLocaleButton($btn, column.visible());
        });


        /**
         * Reset button
         */
        var $resetBtn = $('a.js-btn-reset');
        $resetBtn.unbind('click');
        $resetBtn.on('click', function (ev) {
            if(!confirm('Reset translations?')) {
                ev.preventDefault();
            }
            else {
                $dataTable.state.clear();
            }
        });


        /**
         * Upload button
         */
        var $uploadBtn = $('a.js-btn-upload');
        $uploadBtn.unbind('click');
        $uploadBtn.on('click', function (ev) {
            ev.preventDefault();
            $filesInput.click();
        });


        /**
         * Save button
         */
        var $saveBtn = $('a.js-btn-save');
        $saveBtn.unbind('click');
        $saveBtn.on('click', function (ev) {
            ev.preventDefault();

            var url = $(this).data('url');
            ModalForm.show(url, 'davamigo_translator_save_form');
        });


        /**
         * Input files
         */
        var $filesInput = $('.js-files-input');
        $filesInput.unbind('change');
        $filesInput.on('change', function() {
            var $form = $('.js-files-form');
            $form.submit();
        });
    };


    /**
     * enableLocaleButton()
     * @param {object} $btn
     * @param {bool}   enable
     */
    var enableLocaleButton = function($btn, enable) {

        if (enable) {
            $btn.removeClass('btn-default').addClass('btn-info');
        }
        else {
            $btn.removeClass('btn-info').addClass('btn-default');
        }
    };


    /**
     * Main process
     */
    try {
        init();
    }
    catch (err) {
        // Do nothing
    }


    /**
     * Publig functions
     */
    return {
        init: init
    };

}(document, jQuery));
