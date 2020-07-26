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
        // Contact state variables
        $this->RegisterPropertyInteger('StateVariable', 0);
        // Decrease variables
        $this->RegisterPropertyInteger('Delay', 30);
        $this->RegisterPropertyBoolean('OpenValve', false);
        $this->RegisterPropertyInteger('Level', 0);
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
        if ($this->ReadPropertyInteger('StateVariable') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('StateVariable'), VM_UPDATE);
        }
        //Never delete this line!
        parent::ApplyChanges();
        //Create our trigger
        if (IPS_VariableExists($this->ReadPropertyInteger('StateVariable'))) {
            $this->RegisterMessage($this->ReadPropertyInteger('StateVariable'), VM_UPDATE);
        }
    }

    /**
     * Internal SDK funktion.
     * data[0] = new value
     * data[1] = value changed?
     * data[2] = old value
     * data[3] = timestamp.
     */
    public function MessageSink($timeStamp, $senderID, $message, $data)
    {
        // $this->SendDebug('MessageSink', 'SenderId: '. $senderID . ' Data: ' . print_r($data, true), 0);
        switch ($message) {
            case VM_UPDATE:
                // Safty Check
                if ($senderID != $this->ReadPropertyInteger('StateVariable')) {
                    $this->SendDebug('MessageSink', 'SenderID: ' . $senderID . ' unbekannt!');
                    break;
                }
                // Zustandsaenderung ?
                if ($data[0] == 1 && $data[1] == true) { // OnChange auf 1, d.h. OPEN
                    $this->SendDebug('MessageSink', 'OnChange auf <OPEN> geschalten');
                    $delay = $this->ReadPropertyInteger('Delay');
                    if ($delay > 0) {
                        $this->SetTimerInterval('DelayTrigger', 1000 * $delay);
                    } else {
                        $this->Decrease();
                    }
                } elseif ($data[0] == 0 && $data[1] == true) { // OnChange auf 0, d.h. CLOSE
                    $this->SendDebug('MessageSink', 'OnChange auf <CLOSE> geschalten');
                    $this->Restore();
                } else { // OnChange - keine Zustandsaenderung
                    $this->SendDebug('MessageSink', 'OnChange unveraendert - keine Zustandsaenderung');
                }
            break;
          }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSC_Decrease($id);
     */
    public function Decrease()
    {
        $this->SendDebug('Decrease', 'wurde aufgerufen!');
        // Timer so oder so deaktivieren
        $this->SetTimerInterval('DelayTrigger', 0);
        // 1.Bedingung auslesen (Ventilposition)
        if ($this->ReadPropertyBoolean('OpenValve')) {
            $lid = $this->ReadPropertyInteger('Level');
            if ($lid != 0) {
                if (GetValue($lid) <= 0) {
                    $this->SendDebug('Decrease', 'Ventilpostionscheck ist aktiv und traf zu!');
                    return;
                }
            } else {
                $this->SendDebug('Decrease', 'Ventilpostionscheck ist aktiv aber keine Positionsvariable hinterlegt!');
            }
        }
        // 2.Bedingung auslesen (Temperaturunterschied)
        if ($this->ReadPropertyBoolean('TempDiff')) {
            // Temperaturwert
            $diff = $this->ReadPropertyInteger('Difference');
            // IDs
            $iid = $this->ReadPropertyInteger('TempIndoor');
            $oid = $this->ReadPropertyInteger('TempOutdoor');
            if (($iid != 0) & ($oid != 0)) {
                if ((GetValue($iid) - GetValue($oid)) < $diff) {
                    $this->SendDebug('Decrease', 'Temperaturcheck ist aktiv und traf zu!');
                    return;
                }
            } else {
                $this->SendDebug('Decrease', 'Temperaturcheck ist aktiv aber keine Temperaturvariable hinterlegt!');
            }
        }
        // Thermostat manuell auf "12 °C" stellen
        $radiator = $this->ReadPropertyInteger('Radiator1', 0);
        if ($radiator != 0) {
            $ret = HM_WriteValueInteger($radiator, 'CONTROL_MODE', 1);
            $ret = HM_WriteValueFloat($radiator, 'SET_POINT_TEMPERATURE', 12);
            $this->SendDebug('Decrease', 'Set Radiator1 control mode to MANUAL => ' . boolval($ret));
        }
        $radiator = $this->ReadPropertyInteger('Radiator2', 0);
        if ($radiator != 0) {
            $ret = HM_WriteValueInteger($radiator, 'CONTROL_MODE', 1);
            $ret = HM_WriteValueFloat($radiator, 'SET_POINT_TEMPERATURE', 12);
            $this->SendDebug('Decrease', 'Set Radiator2 control mode to MANUAL => ' . boolval($ret));
        }
    }

    /**
     * This function will be available automatically after the module is imported with the module control.
     * Using the custom prefix this function will be callable from PHP and JSON-RPC through:.
     *
     * TSC_Restore($id);
     */
    public function Restore()
    {
        $this->SendDebug('Restore', 'wurde aufgerufen!');
        // Läuft vielleicht ein Timer?
        if ($this->GetTimerInterval('DelayTrigger') > 0) {
            // Timer deaktivieren
            $this->SetTimerInterval('DelayTrigger', 0);
            // und nix mehr machen
            return;
        }
        // Thermostat wieder auf "AUTO" stellen
        $radiator = $this->ReadPropertyInteger('Radiator1', 0);
        if ($radiator != 0) {
            $ret = HM_WriteValueInteger($radiator, 'CONTROL_MODE', 0);
            $this->SendDebug('Restore', 'Set Radiator1 control mode to auto => ' . boolval($ret));
        }
        $radiator = $this->ReadPropertyInteger('Radiator2', 0);
        if ($radiator != 0) {
            $ret = HM_WriteValueInteger($radiator, 'CONTROL_MODE', 0);
            $this->SendDebug('Restore', 'Set Radiator2 control mode to auto => ' . boolval($ret));
        }
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
