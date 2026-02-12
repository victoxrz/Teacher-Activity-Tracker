<?php

namespace local_teacher_activities\services;

use local_teacher_activities\models\submission;
use local_teacher_activities\models\submission_item;
use stdClass;
use local_teacher_activities\forms\activity_form;
use local_teacher_activities\services\form_helper;

class api
{
    public static function get_submission(int $submissionid)
    {
        $to_return = [];
        $submission_data = new submission($submissionid);
        $to_return['personal_data']['general_data'] = $submission_data->to_defined_record();

        $submission_items = submission_item::get_records(['submissionid' => $submission_data->get('id')], 'sectionkey, activitykey');


        foreach ($submission_items as $item) {
            $sectionkey = $item->get('sectionkey');
            $activitykey = $item->get('activitykey');

            if (!isset($to_return[$sectionkey][$activitykey])) {
                $to_return[$sectionkey][$activitykey] = new stdClass();
            }
            $current_activity = $to_return[$sectionkey][$activitykey];

            $item_count = &$current_activity->{activity_form::REPEAT_HIDDEN_NAME};
            if (!isset($item_count)) {
                $item_count = 0;
            }

            if ($item->get('activitysubkey') !== null)
                foreach (self::get_form_types($item) as $itemkey => $itemvalue) {
                    if (!isset($current_activity->{$itemkey})) {
                        $current_activity->{$itemkey} = [];
                    }
                    $current_activity->{$itemkey}[$item_count] = $itemvalue;
                }

            if ($item->get('vars') !== null && $item->get('activitysubkey') !== null) {
                $itemtypes = explode('-', $item->get('activitysubkey'));
                foreach (self::get_form_vars(
                    $item,
                    $itemtypes[\count($itemtypes) - 1]
                ) as $varkey => $varvalue) {
                    if (!isset($current_activity->{$varkey})) {
                        $current_activity->{$varkey} = [];
                    }
                    $current_activity->{$varkey}[$item_count] = $varvalue;
                }
            }

            $current_activity->itemlink[$item_count] = $item->get('urladdress');

            $draftid = 0;
            file_prepare_draft_area(
                $draftid,
                \context_system::instance()->id,
                'local_teacher_activities',
                'submission_item_files',
                $item->get('id'),
                activity_form::get_filemanager_options()
            );

            $current_activity->id[$item_count] = $item->get('id');

            if (!isset($current_activity->itemfile[$item_count])) {
                $current_activity->itemfile[$item_count] = $draftid;
            }

            $item_count++;
        }
        return $to_return;
    }

    private static function get_form_types(submission_item $item)
    {
        $key = $item->get('activitykey');
        $subkey = explode('-', $item->get('activitysubkey'));
        $result = [];
        $keys = array_merge([$key], $subkey);
        for ($i = 0; $i < \count($keys) - 1; $i++) {
            $result[activity_form::PREFIX_ITEMTYPE . $keys[$i]] = $keys[$i + 1];
        }
        return $result;
    }

    private static function get_form_vars(submission_item $item, string $activitykey)
    {
        $vars = json_decode($item->get('vars'), true);
        $result = [];
        foreach ($vars as $key => $value) {
            $result[activity_form::PREFIX_VAR . "{$key}-{$activitykey}"] = $value;
        }
        return $result;
    }

    /**
     * 
     * @param array<string, array<int, \stdClass>> $submission_data
     * @return void
     */
    public static function save_submission($submission_data)
    {
        // Logic to save submission data to the database
        // https://moodledev.io/docs/5.2/apis/subsystems/form/usage/files#store-updated-set-of-files
        if (
            !isset($submission_data['personal_data']) ||
            !isset($submission_data['personal_data']['general_data'])
        ) {
            throw new \InvalidArgumentException('Personal data is required to save submission.');
        }
        $personalData = new submission(
            $submission_data['personal_data']['general_data']->id ?? 0,
            $submission_data['personal_data']['general_data']
        );
        unset($submission_data['personal_data']);

        $submission_items = [];
        foreach ($submission_data as $section_key => $activities) {
            foreach ($activities as $activity_key => $data) {
                $itemcount = $data->{activity_form::REPEAT_HIDDEN_NAME} ?? 0;

                for ($i = 0; $i < $itemcount; $i++) {
                    $id = $data->id[$i] ?? 0;
                    $item = new submission_item($id); // Load existing record to update
                    if ($item->is_evaluted()) {
                        continue; // Skip items that have already been evaluated
                    }

                    $item->from_record((object) [
                        'sectionkey' => $section_key,
                        'activitykey' => $activity_key,
                        'activitysubkey' => form_helper::get_item_types($data, $activity_key, $i),
                        'vars' => json_encode(form_helper::get_item_vars($data, $i)),
                        'details' => json_encode(form_helper::get_item_details($data, $i)),
                        'urladdress' => empty($data->itemlink[$i]) ? null : $data->itemlink[$i],
                        'evaluationscore' => null,
                    ]);

                    $submission_items[] = [
                        'persistent' => $item,
                        'draftid' => $data->itemfile[$i]
                    ];
                }
            }
        }

        global $DB;

        try {
            $transaction = $DB->start_delegated_transaction();

            $personalData->save();
            foreach ($submission_items as $item) {
                $item['persistent']->set('submissionid', $personalData->get('id'));
                $item['persistent']->save();

                file_save_draft_area_files(
                    $item['draftid'],
                    \context_system::instance()->id,
                    'local_teacher_activities',
                    'submission_item_files',
                    $item['persistent']->get('id'),
                    activity_form::get_filemanager_options()
                );
            }

            $transaction->allow_commit();
        } catch (\Exception $e) {
            // Extra cleanup steps.
            // Re-throw exception after commiting.
            $transaction->rollback($e);
        }
    }
}