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
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2024 Andreas Grabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/fragment', 'core/templates', 'core/notification'], function(
    $, fragment, templates, notification) {

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
            this.serviceparams = { };
        } else {
            this.serviceparams = serviceparams;
        }
        this.contextid = contextid;
        this.isshown = false;
    };

    /**
     * Load content by the fragment api
     * @param {string} adjacentPosition Can be on of the following values: afterbegin, afterend, beforebegin, beforeend.
     * @returns {Promise}
     */
    ContentLoader.prototype.loadContent = function(adjacentPosition) {
        var _this = this; // We have to save this because the context in the promise is another.
        // Show a spinner while loading the table if not disabled.
        if (_this.disablespinner === undefined) {
            $(_this.contentcontainerselector).html('');
            var spinnerhtml = '<div class="text-center" id="myspinner"><i class="fa fa-spinner fa-2x fa-spin"></i></div>';
            $(_this.contentcontainerselector).prepend(spinnerhtml);
        }
        var fragmentpromise = fragment.loadFragment('unilabeltype_grid', _this.fragmentcall, _this.contextid, _this.serviceparams);
        return fragmentpromise.then(function(html, js) {
            var container = document.querySelector(_this.contentcontainerselector);
            container.insertAdjacentHTML(adjacentPosition, html);
            if (js) {
                templates.runTemplateJS(js);
            }
            _this.isshown = true;
            $('#myspinner').remove();
            return true;
        }).fail(notification.exception);
    };

    /**
     * Load html content on the given event.
     * @param {string} triggerselector
     * @param {string} triggerevent
     * @param {string} adjacentPosition Can be on of the following values: afterbegin, afterend, beforebegin, beforeend.
     */
    ContentLoader.prototype.autoload = function(triggerselector, triggerevent, adjacentPosition) {
        var _this = this;
        $(triggerselector).on(triggerevent, function() {
            if (_this.isshown == false) {
                _this.loadContent(adjacentPosition);
                _this.isshown = true;
            }
        });
    };

    return ContentLoader;

});
