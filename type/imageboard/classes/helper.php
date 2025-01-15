<?php
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
 * Unilabel type imageboard
 *
 * @package     unilabeltype_imageboard
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_imageboard;

/**
 * Helper class
 *
 * @package     unilabeltype_imageboard
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Creates the data to be rendered from mustache to create the grid.
     *
     * @param int $canvaswidth
     * @param int $canvasheight
     * @param int $xsteps
     * @param int $ysteps
     * @return array
     */
    public static function createdataforhelpergrid($canvaswidth = 600, $canvasheight = 400, $xsteps = 50, $ysteps = 50): array {
        $helpergrids = [];
        for ($y = 0; $y < $canvasheight; $y += $ysteps) {
            for ($x = 0; $x < $canvaswidth; $x += $xsteps) {
                $helpergrid = [];
                $helpergrid['x'] = $x;
                $helpergrid['y'] = $y;

                // Calculate the last step, which can be smaller then a step.
                if ($x + $xsteps > $canvaswidth) {
                    $helpergrid['xsteps'] = ($x + $xsteps) - $canvaswidth;
                }
                if ($y + $ysteps > $canvasheight) {
                    $helpergrid['ysteps'] = ($y + $ysteps) - $canvasheight;
                }

                $helpergrids[] = $helpergrid;
            }
        }
        return $helpergrids;
    }
}
