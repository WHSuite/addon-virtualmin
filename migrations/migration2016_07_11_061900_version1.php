<?php
namespace Addon\Virtualmin\Migrations;

use \App\Libraries\BaseMigration;

class Migration2016_07_11_061900_version1 extends BaseMigration
{
    public function up($addon_id)
    {
        // Server Module
        $module = new \ServerModule;
        $module->name = 'Virtualmin';
        $module->slug = 'virtualmin';
        $module->addon_id = $addon_id;
        $module->save();

        // Data Group
        $data_group = new \DataGroup();
        $data_group->slug = 'serverdata_virtualmin';
        $data_group->name = 'virtualmin_server_custom_fields';
        $data_group->addon_id = $addon_id;
        $data_group->is_editable = 0;
        $data_group->is_active = 1;
        $data_group->save();

        // Data fields
        \DataField::insert(
            array(
                array(
                    'slug' => 'virtualmin_server_port',
                    'title' => 'virtualmin_server_port',
                    'data_group_id' => $data_group->id,
                    'help_text' => '',
                    'type' => 'text',
                    'is_editable' => 1,
                    'is_staff_only' => 1,
                    'validation_rules' => 'required',
                    'sort' => 1,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                ),
                array(
                    'slug' => 'virtualmin_server_disable_verify',
                    'title' => 'virtualmin_server_disable_verify',
                    'data_group_id' => $data_group->id,
                    'help_text' => 'virtualmin_server_disable_verify_help',
                    'type' => 'checkbox',
                    'is_editable' => 1,
                    'is_staff_only' => 1,
                    'validation_rules' => 'required',
                    'sort' => 1,
                    'created_at' => $this->date,
                    'updated_at' => $this->date
                )
            )
        );
    }

    public function down($addon_id)
    {
        \ServerModule::where('addon_id', '=', $addon_id)->delete();

        $data_group = \DataGroup::where('slug', '=', 'serverdata_virtualmin')->first();

        $data_fields = $data_group->DataField()->get();
        foreach ($data_fields as $field) {
            $field_values = $field->DataFieldValue()->delete();

            $field->delete();
        }

        $data_group->delete();
    }
}
