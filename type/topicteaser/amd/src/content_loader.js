// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * unilabel type topicteaser
 *
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2022 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
        'jquery',
        'core/fragment',
        'core/templates',
        'core/notification'
    ],
    function($, fragment, templates, notification) {

        /**
         * Class constructor
         * @param {string} contentcontainerselector
         * @param {string} fragmentcall
         * @param {object} serviceparams
         * @param {int} contextid
         */
        var ContentLoader = function(contentcontainerselector, fragmentcall, serviceparams, contextid) {
            this.contentcontainerselector = contentcontainerselector;
            this.fragmentcall = fragmentcall;
            if (serviceparams === undefined) {
                this.serviceparams = {};
            } else {
                this.serviceparams = serviceparams;
            }
            this.contextid = contextid;
            this.isshown = false;
        };

        /**
         * Load a html content inside the container.
         */
        ContentLoader.prototype.loadContent = function() {
            var _this = this; // We have to save this because the context in the promise is another.
            _this._load();
        };

        /**
         * Replace the container with new html content.
         */
        ContentLoader.prototype.replaceContent = function() {
            var _this = this; // We have to save this because the context in the promise is another.
            _this._load(true);
        };

        /**
         * Load content by the fragment api
         * @param {bool} replace
         * @returns {promise}
         */
        ContentLoader.prototype._load = function(replace) {
            var _this = this; // We have to save this because the context in the promise is another.
            // Show a spinner while loading the table.
            $(_this.contentcontainerselector).html('');
            var spinnerhtml = '<div class="text-center" id="myspinner"><i class="fa fa-spinner fa-2x fa-spin"></i></div>';
            $(_this.contentcontainerselector).prepend(spinnerhtml);
            var fragmentpromise = fragment.loadFragment(
                'unilabeltype_topicteaser',
                _this.fragmentcall,
                _this.contextid,
                _this.serviceparams
            );
            return fragmentpromise.then(function(html, js) {
                if (replace) {
                    $(_this.contentcontainerselector).replaceWith(html);
                } else {
                    $(_this.contentcontainerselector).html(html);
                }
                if (js) {
                    templates.runTemplateJS(js);
                }
                _this.isshown = true;
                $('#myspinner').remove();
                return;
            }).fail(notification.exception);
        };

        /**
         * Load html content on the given event.
         * @param {string} triggerselector
         * @param {string} triggerevent
         */
        ContentLoader.prototype.autoload = function(triggerselector, triggerevent) {
            var _this = this;
            $(triggerselector).on(triggerevent, function() {
                if (_this.isshown == false) {
                    _this.loadContent();
                    _this.isshown = true;
                }
            });
        };

        // Return an init method to call it directly from a php file.
        return {
            'init': function(
                contentcontainerselector,
                fragmentcall,
                serviceparams,
                contextid,
                triggerselector,
                triggerevent
            ) {
                var contentLoader = new ContentLoader(contentcontainerselector, fragmentcall, serviceparams, contextid);
                contentLoader.autoload(triggerselector, triggerevent);
            }
        };
    }
);