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

namespace mod_unilabel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Tests for the shared content type helpers.
 *
 * @package    mod_unilabel
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(content_type::class)]
final class content_type_test extends \advanced_testcase {
    /**
     * Render the colour picker with the given stored value and return its html.
     *
     * @param string $value stored colour value
     * @return string
     */
    private function render_colourpicker(string $value): string {
        global $PAGE;
        $PAGE->set_url('/mod/unilabel/edit_content.php');
        $PAGE->set_context(\context_system::instance());
        $mform = new \MoodleQuickForm('test', 'post', '');
        $type = new \unilabeltype_carousel\content_type();
        $type->add_colourpicker($mform, 'background', 'Background', $value);
        return $mform->_elements[0]->_text;
    }

    #[Group('baseline')]
    /**
     * A stored value cannot break out of the value attribute.
     *
     * @return void
     */
    public function test_colourpicker_escapes_stored_value(): void {
        $this->resetAfterTest();

        $evilstr = '#fff"><img src=x onerror=alert(1)>';
        $quotedstr = htmlspecialchars($evilstr, ENT_QUOTES);

        $html = $this->render_colourpicker($evilstr);

        $this->assertStringNotContainsString($evilstr, $html);
        $this->assertStringContainsString($quotedstr, $html);
    }

    #[Group('baseline')]
    /**
     * A regular colour value is rendered unchanged.
     *
     * @return void
     */
    public function test_colourpicker_keeps_regular_value(): void {
        $this->resetAfterTest();

        $colorstringstocheck = [
            '#AABBCC',
            'green',
            'rgb(255,0,0)',
            'rgba(255,0,0,0.5)',
            'hsl(120,100%,50%)',
            'hsla(60,100%,50%,0.5)',
        ];
        foreach ($colorstringstocheck as $colorstring) {
            $this->assertStringContainsString($colorstring, $this->render_colourpicker($colorstring));
        }
    }
}
