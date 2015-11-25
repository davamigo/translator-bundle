/**
 * @file bundles/ifraktaltranslator/jsmodal.js
 * @author davamigo@gmail.com
 */

var ModalForm = (function (doc, $) {

    'use strict';

    var op = {
        formId:          '',
        formSelector:    '',
        modalSelector:   '',
        successCallback: null,
        errorCallback:   null
    };


    /**
     * showModal()
     *
     * @param {string} route
     * @param {string} formId
     * @param [successCallback]
     * @param [errorCallback]
     */
    var showModal = function(route, formId, successCallback, errorCallback) {

        op.formId           = formId;
        op.formSelector     = '.js-' + formId;
        op.modalSelector    = '.js-' + formId + '-modal';
        op.successCallback  = op.successCallback || successCallback;
        op.errorCallback    = op.errorCallback || errorCallback;

        $.post(route)

            .success(function(data) {
                onLoadContent(data)
            })

            .fail(function(jqxhr, textStatus, error) {
                onFail(error);
            });
    };


    /**
     * hideModal()
     */
    var hideModal = function() {
        var $modal = $(op.modalSelector);
        $modal.each(function() {
            var $this = $(this);
            $this.modal('hide');
        });
    };


    /**
     * onLoadContent()
     *
     * @param data
     */
    var onLoadContent = function(data) {

        var $modal = $(op.modalSelector);
        if ($modal.length > 0) {
            hideModal();
            setTimeout(function() { onLoadContent(data); }, 1000);
        }
        else {

            $(data).appendTo('body');

            var $form = $(op.formSelector);
            if($form.length > 0) {
                $form.unbind('submit');
                $form.on('submit', function(ev) {
                    ev.preventDefault();
                    onSubmit($form);
                });
            }

            $modal = $(op.modalSelector);
            if($modal.length > 0) {

                $modal.modal();

                $modal.on('hidden.bs.modal', function(ev) {
                    ev.preventDefault();
                    $modal.remove();
                });
            }
        }
    };


    /**
     * onSubmit()
     *
     * @param $form
     */
    var onSubmit = function($form) {

        hideModal();

        var action = $form.attr('action');
        var formData = $form.serializeArray();

        $.post(action, formData)

            .success(function(data) {
                onSuccessSubmit(data);
            })

            .fail(function(jqxhr, textStatus, error) {
                onFail(error);
            });
    };


    /**
     * onSuccessSubmit()
     *
     * @param data
     */
    var onSuccessSubmit = function(data) {

        if (typeof data == 'string') {
            onLoadContent(data);
        }
        else if (!data.result) {
            onFail(data.message);
        }
        else if (typeof op.successCallback == 'function') {
            op.successCallback(data);
        }
        else {
            showMessage(data.message, 'success');
        }
    };


    /**
     * onFail()
     *
     * @param message
     */
    var onFail = function(message) {

        if (typeof op.errorCallback == 'function') {
            op.errorCallback(message);
        }
        else {
            showMessage(message, 'error');
        }
    };


    /**
     * showMessage()
     *
     * @param message
     * @param type
     */
    var showMessage = function(message, type) {

        if (type == 'error') {
            if (!message) {
                message = 'Internal server error';
            }
            alert('Error: ' + message);
        }
        else if (!message) {
            alert(message);
        }
    };


    /**
     * Main process
     */

    return {
        show: showModal,
        hide: hideModal
    };

}(document, $));
