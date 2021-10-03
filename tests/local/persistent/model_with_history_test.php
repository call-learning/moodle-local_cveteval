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

namespace local_cveteval\local\persistent;

use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\test\assessment_test_trait;

defined('MOODLE_INTERNAL') || die();

/**
 * Historical model test.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class model_with_history_test extends \advanced_testcase {
    use assessment_test_trait;

    public function test_with_history_disabled() {
        $this->resetAfterTest();
        history_entity::disable_history_globally();

        // Import model 1.
        $sample = $this->get_simple_model_1();
        $this->create_simple_model($sample->criteria, $sample->situations, $sample->evalplans);
        $evalgrid = \local_cveteval\local\persistent\evaluation_grid\entity::get_record(
            [
                'idnumber' => 'evalgrid'
            ]);

        $this->assertCount(1, situation\entity::get_records());
        $this->assertEquals(1, situation\entity::count_records());
        $this->assertCount(2, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertCount(2,
            criterion\entity::get_records_select("evalgridid = :evalgridid", ['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(2, criterion\entity::count_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertCount(1, planning\entity::get_records());
        $this->assertEquals(1, planning\entity::count_records());

        // Now import model 2.
        $sample = $this->get_simple_model_2();
        $this->create_simple_model($sample->criteria, $sample->situations, $sample->evalplans);
        $this->assertCount(2, situation\entity::get_records());
        $this->assertEquals(2, situation\entity::count_records());
        $this->assertCount(4, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertCount(4,
            criterion\entity::get_records_select("evalgridid = :evalgridid", ['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(4, criterion\entity::count_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(1, planning\entity::count_records());
        $this->assertEquals(1, planning\entity::count_records());
    }

    public function test_with_history_enabled() {
        $this->resetAfterTest();
        $cevetevalgenerator = $this->getDataGenerator()->get_plugin_generator('local_cveteval');
        // The evaluation grid will be for all history.
        $cevetevalgenerator->create_evaluation_grid([
            'name' => 'evalgrid',
            'idnumber' => 'evalgrid'
        ]);
        // Now create a new history.
        $firsthistory = $this->create_history();
        history_entity::set_current_id($firsthistory->get('id'));
        // Import model 1.
        $sample = $this->get_simple_model_1();
        $this->create_simple_model($sample->criteria, $sample->situations, $sample->evalplans);

        $evalgrid = \local_cveteval\local\persistent\evaluation_grid\entity::get_record([
                'idnumber' => 'evalgrid'
            ]);
        $extractidnumber = function($entity) {
            return $entity->get('idnumber');
        };
        $this->assertCount(1, situation\entity::get_records());
        $this->assertEquals(1, situation\entity::count_records());
        $this->assertCount(2, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(2, criterion\entity::count_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(42, criterion\entity::count_records()); // Default criteria: 40.
        $this->assertEquals(['criterion1', 'criterion1bis'],
            array_map($extractidnumber, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')])));
        $this->assertCount(2,
            criterion\entity::get_records_select("evalgridid = :evalgridid", ['evalgridid' => $evalgrid->get('id')]));
        $this->assertCount(1, planning\entity::get_records());
        $this->assertEquals(1, planning\entity::count_records());

        // Now a new history.
        $secondhistory = $this->create_history();
        history_entity::set_current_id($secondhistory->get('id'));
        // Now import model 2.
        $sample = $this->get_simple_model_2();
        $this->create_simple_model($sample->criteria, $sample->situations, $sample->evalplans);

        $this->assertCount(1, situation\entity::get_records());
        $this->assertEquals(1, situation\entity::count_records());
        $this->assertCount(2, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(2, criterion\entity::count_records(['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(42, criterion\entity::count_records()); // Default criteria: 40.
        $this->assertCount(2,
            criterion\entity::get_records_select("evalgridid = :evalgridid", ['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(['criterion2', 'criterion2bis'],
            array_map($extractidnumber, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')])));
        $this->assertEquals(0, planning\entity::count_records());
        $this->assertEquals(0, planning\entity::count_records());


        // All are active now.
        $firsthistory->set('isactive', true);
        $firsthistory->save();
        $secondhistory->set('isactive', true);
        $firsthistory->save();
        history_entity::reset_current_id();
        $this->assertCount(2, situation\entity::get_records());
        $this->assertEquals(2, situation\entity::count_records());
        $this->assertCount(4,
            criterion\entity::get_records_select("evalgridid = :evalgridid", ['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(['criterion1', 'criterion1bis', 'criterion2', 'criterion2bis'],
            array_map($extractidnumber, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')])));
        $this->assertEquals(44, criterion\entity::count_records()); // Default criteria: 40.
        $this->assertEquals(1, planning\entity::count_records());
        $this->assertEquals(1, planning\entity::count_records());

        // Now disable the first history.
        $firsthistory->set('isactive', 0);
        $firsthistory->update();
        $this->assertCount(1, situation\entity::get_records());
        $this->assertEquals(1, situation\entity::count_records());
        $this->assertCount(2,
            criterion\entity::get_records_select("evalgridid = :evalgridid", ['evalgridid' => $evalgrid->get('id')]));
        $this->assertEquals(['criterion2', 'criterion2bis'],
            array_map($extractidnumber, criterion\entity::get_records(['evalgridid' => $evalgrid->get('id')])));
        $this->assertEquals(42, criterion\entity::count_records()); // Default criteria: 40.
        $this->assertEquals(0, planning\entity::count_records());
        $this->assertEquals(0, planning\entity::count_records());
        // And then the second.
        $secondhistory->set('isactive', 0);
        $secondhistory->update();
        $this->assertEquals(0, situation\entity::count_records());
        $this->assertEquals(0, planning\entity::count_records());
        $this->assertEquals(0, planning\entity::count_records());
        $this->assertEquals(40, criterion\entity::count_records()); // Default criteria: 40.
    }

}