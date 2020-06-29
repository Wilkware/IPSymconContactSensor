<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/traits.php';  // Allgemeine Funktionen

// CLASS ContactSensor
class ContactSensor extends IPSModule
{
    use ProfileHelper;
    use DebugHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        // Decrease variables
        $this->RegisterPropertyInteger('Delay', 30);
        $this->RegisterPropertyBoolean('OpenValve', false);
        $this->RegisterPropertyBoolean('TempDiff', false);
        $this->RegisterPropertyInteger('Difference', 10);
        // Radiator variables
        $this->RegisterPropertyInteger('Radiator1', 0);
        $this->RegisterPropertyInteger('Radiator2', 0);
        // Clima variables
        $this->RegisterPropertyInteger('TempIndoor', 0);
        $this->RegisterPropertyInteger('TempOutdoor', 0);
        // Dashboard variables
        $this->RegisterPropertyInteger('ScriptMessage', 0);
        $this->RegisterPropertyString('RoomName', $this->Translate('Unknown'));
        $this->RegisterPropertyInteger('LifeTime', 0);
        // Update trigger
        $this->RegisterTimer('DelayTrigger', 0, "TCS_Decrease(\$_IPS['TARGET']);");
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSC_Decrease($id);
     */
    public function Decrease()
    {
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TCS_Delay($id, $duration);
     *
     * @param int $duration Wartezeit einstellen.
     */
    public function Delay(int $duration)
    {
        IPS_SetProperty($this->InstanceID, 'Delay', $duration);
        IPS_ApplyChanges($this->InstanceID);
    }
}
