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
 * Script to help developers.
 *
 * Generates the necessary embed code and show question url.
 *
 * @package   filter_embedquestion
 * @copyright 2018 The Open University - based on question/preview.php
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/formslib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/filter/embedquestion/testhelper.php');
$PAGE->set_heading('Embed question filter test helper script');
$PAGE->set_title('Embed question filter test helper script');

require_login();
require_capability('moodle/site:config', context_system::instance());

class filter_embedquestion_test_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'id', 'Question id');
        $mform->setType('id', PARAM_INT);

        $behaviours = question_engine::get_archetypal_behaviours();
        foreach ($behaviours as $behaviour => $name) {
            if (!question_engine::can_questions_finish_during_the_attempt($behaviour)) {
                unset($behaviours[$behaviour]);
            }
        }
        $mform->addElement('select', 'behaviour', 'Behaviour', $behaviours);

        $this->add_action_buttons(false, 'Generate information');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // TODO validate ids and options.

        return $errors;
    }
}

$form = new filter_embedquestion_test_form();

echo $OUTPUT->header();

if ($fromform = $form->get_data()) {
    $question = question_bank::load_question($fromform->id);
    $context = context::instance_by_id($question->contextid);

    $options = new filter_embedquestion\question_options($question,
            $context->get_course_context()->instanceid, $fromform->behaviour);

    echo $OUTPUT->heading('Information for embedding question ' . format_string($question->name));

    $iframeurl = $options->get_page_url($question->id);
    echo html_writer::tag('p', 'Link to show the question in the iframe: ' .
            html_writer::link($iframeurl, $iframeurl));

    echo html_writer::tag('p', 'Code to embed the question: TODO');
}

echo $OUTPUT->heading('Generate code an links for embedding a question.');
echo $form->render();

echo $OUTPUT->footer();

