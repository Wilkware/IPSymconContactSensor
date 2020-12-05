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
        $this->RegisterPropertyInteger('StateVariable2', 0);
        // Decrease variables
        $this->RegisterPropertyInteger('Delay', 30);
        $this->RegisterPropertyBoolean('OpenValve', false);
        $this->RegisterPropertyInteger('Level', 0);
        $this->RegisterPropertyBoolean('TempDiff', false);
        $this->RegisterPropertyInteger('Difference', 10);
        $this->RegisterPropertyBoolean('RepeatCheck', false);
        $this->RegisterPropertyInteger('RepeatTime', 1);
        $this->RegisterPropertyBoolean('SwitchBack', false);
        $this->RegisterPropertyInteger('SwitchTime', 60);
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
        // Delay trigger
        $this->RegisterTimer('DelayTrigger', 0, "IPS_RequestAction(\$_IPS['TARGET'],'Delay', 0);");
        // Repeat trigger
        $this->RegisterTimer('RepeatTrigger', 0, "IPS_RequestAction(\$_IPS['TARGET'],'Repeat', 0);");
        // Switch trigger
        $this->RegisterTimer('SwitchTrigger', 0, "IPS_RequestAction(\$_IPS['TARGET'],'Switch', 0);");
        // Internal state
        $this->RegisterAttributeBoolean('Reduction', false);
        $this->RegisterAttributeInteger('Sensors', 0);
        $this->RegisterAttributeInteger('Message', 0);
    }

    public function ApplyChanges()
    {
        if ($this->ReadPropertyInteger('StateVariable') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('StateVariable'), VM_UPDATE);
        }
        if ($this->ReadPropertyInteger('StateVariable2') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('StateVariable2'), VM_UPDATE);
        }
        //Never delete this line!
        parent::ApplyChanges();
        //Create our trigger
        if (IPS_VariableExists($this->ReadPropertyInteger('StateVariable'))) {
            $this->RegisterMessage($this->ReadPropertyInteger('StateVariable'), VM_UPDATE);
        }
        if (IPS_VariableExists($this->ReadPropertyInteger('StateVariable2'))) {
            $this->RegisterMessage($this->ReadPropertyInteger('StateVariable2'), VM_UPDATE);
        }
        // Set Internal State
        $this->InternalState();
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
                if ($senderID == $this->ReadPropertyInteger('StateVariable')) {
                    $this->SendDebug('MessageSink', 'Kontaktsender 1: #' . $senderID . ', New: ' . $data[0] . ', Changed: ' . var_export($data[1], true) . ', Old: ' . $data[2], 0);
                    // Zustandsänderung ?
                    if ($data[0] == 1 && $data[1] == true) { // State auf 1, d.h. OPEN
                        $this->SendDebug('MessageSink', 'Kontaktsender 1: State auf <OPEN> geschalten');
                        $this->Open(1);
                    } elseif ($data[0] == 0 && $data[1] == true) { // State auf 0, d.h. CLOSE
                        $this->SendDebug('MessageSink', 'Kontaktsender 1: State auf <CLOSE> geschalten');
                        $this->Close(1);
                    } else { // OnChange - keine Zustandsaenderung
                        $this->SendDebug('MessageSink', 'Kontaktsender 1: State unveraendert - keine Zustandsänderung');
                    }
                } elseif ($senderID == $this->ReadPropertyInteger('StateVariable2')) {
                    $this->SendDebug('MessageSink', 'Kontaktsender 2: #' . $senderID . ', New: ' . $data[0] . ', Changed: ' . var_export($data[1], true) . ', Old: ' . $data[2], 0);
                    // Zustandsänderung ?
                    if ($data[0] == 1 && $data[1] == true) { // State auf 1, d.h. OPEN
                        $this->SendDebug('MessageSink', 'Kontaktsender 2: State auf <OPEN> geschalten');
                        $this->Open(2);
                    } elseif ($data[0] == 0 && $data[1] == true) { // State auf 0, d.h. CLOSE
                        $this->SendDebug('MessageSink', 'Kontaktsender 2: State auf <CLOSE> geschalten');
                        $this->Close(2);
                    } else { // OnChange - keine Zustandsaenderung
                        $this->SendDebug('MessageSink', 'Kontaktsender 2: State unveraendert - keine Zustandsänderung');
                    }
                } else {
                    $this->SendDebug('MessageSink', 'Kontaktsender: #' . $senderID . ' unbekannt!');
                }
            break;
          }
    }

    public function RequestAction($ident, $value)
    {
        // Debug output
        $this->SendDebug('RequestAction', $ident . ' Timer wurde ausgelöst!');
        switch ($ident) {
            case 'Delay':
                $this->Decrease();
            break;
            case 'Repeat':
                $this->Decrease();
            break;
            case 'Switch':
                $this->Restore();
            break;
        }
        return true;
    }

    private function Open($sensor)
    {
        // (1) push sensor to process
        $sensors = $this->ReadAttributeInteger('Sensors');
        $sensors = $sensors ^ $sensor;
        $this->WriteAttributeInteger('Sensors', $sensors);
        // (2) check timer or active reduction
        $delay = $this->ReadPropertyInteger('Delay');
        if ((($delay > 0) && ($this->GetTimerInterval('DelayTrigger') != 0)) || $this->ReadAttributeBoolean('Reduction')) {
            $this->SendDebug('Open', 'Kontaktsender ' . $sensor . ' wollte auslösen, aber ein anderer Sensor war schneller!');
            return;
        }
        // (3) start action
        $this->SendDebug('Open', 'Kontaktsender ' . $sensor . ' hat Prozess ausgelöst!');
        if ($delay > 0) {
            $this->SetTimerInterval('DelayTrigger', 1000 * $delay);
        } else {
            $this->Decrease();
        }
    }

    private function Close($sensor)
    {
        // (1) pop sensor from process
        $sensors = $this->ReadAttributeInteger('Sensors');
        $sensors = $sensors ^ $sensor;
        $this->WriteAttributeInteger('Sensors', $sensors);
        // (2) check if some sensor still in process
        if ($sensors > 0) {
            $this->SendDebug('Close', 'Kontaktsender ' . $sensor . ' wollte aufheben, aber ein anderer Sensor ist noch offen!');
            return;
        }
        // (3) end action
        $this->SendDebug('Close', 'Kontaktsender ' . $sensor . ' hat Prozess beendet!');
        $this->Restore();
    }

    private function Decrease()
    {
        $this->SendDebug('Decrease', 'Funktion wurde aufgerufen!');
        // deactivate timer
        $this->SetTimerInterval('DelayTrigger', 0);
        $this->SetTimerInterval('RepeatTrigger', 0);
        $this->SetTimerInterval('SwitchTrigger', 0);
        // conditional switching
        $condition = true;
        // (1) check ventil position
        if ($this->ReadPropertyBoolean('OpenValve')) {
            $lid = $this->ReadPropertyInteger('Level');
            if ($lid != 0) {
                if (GetValue($lid) <= 0) {
                    $this->SendDebug('Decrease', 'Ventilpostionscheck ist aktiv und traf zu!');
                    $condition = false;
                }
            } else {
                $this->SendDebug('Decrease', 'Ventilpostionscheck ist aktiv aber keine Positionsvariable hinterlegt!');
            }
        }
        // (2) check temperature diff
        if ($this->ReadPropertyBoolean('TempDiff')) {
            $diff = $this->ReadPropertyInteger('Difference');
            $iid = $this->ReadPropertyInteger('TempIndoor');
            $oid = $this->ReadPropertyInteger('TempOutdoor');
            if (($iid != 0) & ($oid != 0)) {
                if ((GetValue($iid) - GetValue($oid)) < $diff) {
                    $this->SendDebug('Decrease', 'Temperaturcheck ist aktiv und traf zu!');
                    $condition = false;
                }
            } else {
                $this->SendDebug('Decrease', 'Temperaturcheck ist aktiv aber keine Temperaturvariable hinterlegt!');
            }
        }
        if ($condition) {
            // HM 'WINDOW_STATE' set to <OPEN>
            $radiator = $this->ReadPropertyInteger('Radiator1');
            if ($radiator != 0) {
                $ret = HM_WriteValueInteger($radiator, 'WINDOW_STATE', 1);
                $this->SendDebug('Decrease', 'Heizkörper 1: #' . $radiator . ' Fensterstatus auf OPEN setzen => ' . var_export($ret, true));
            }
            $radiator = $this->ReadPropertyInteger('Radiator2');
            if ($radiator != 0) {
                $ret = HM_WriteValueInteger($radiator, 'WINDOW_STATE', 1);
                $this->SendDebug('Decrease', 'Heizkörper 2: #' . $radiator . ' Fensterstatus auf OPEN setzen => ' . var_export($ret, true));
            }
            // Internal State
            $this->WriteAttributeBoolean('Reduction', true);
            // Switch back timer
            if ($this->ReadPropertyBoolean('SwitchBack')) {
                $time = $this->ReadPropertyInteger('SwitchTime');
                $this->SetTimerInterval('SwitchTrigger', $time * 60 * 1000);
            }
            // Send message ?
            $this->SendMessage(true);
        } elseif ($this->ReadPropertyBoolean('RepeatCheck')) {
            $time = $this->ReadPropertyInteger('RepeatTime');
            $this->SetTimerInterval('RepeatTrigger', $time * 60 * 1000);
        }
    }

    private function Restore()
    {
        $this->SendDebug('Restore', 'Funktion wurde aufgerufen!');
        // Active delay timer?
        if ($this->GetTimerInterval('DelayTrigger') > 0) {
            // Timer deactivate
            $this->SetTimerInterval('DelayTrigger', 0);
            $this->SendDebug('Restore', 'Ein Verzögerungs-Timer hatte noch nicht ausgelöst!!!');
        }
        // Active repeat timer?
        if ($this->GetTimerInterval('RepeatTrigger') > 0) {
            // Timer deactivate
            $this->SetTimerInterval('RepeatTrigger', 0);
            $this->SendDebug('Restore', 'Ein Wiederholungs-Timer war noch aktiv!!!');
        }
        // Active switch back timer?
        if ($this->GetTimerInterval('SwitchTrigger') > 0) {
            // Timer deactivate
            $this->SetTimerInterval('SwitchTrigger', 0);
            $this->SendDebug('Restore', 'Ein Aufhebungs-Timer war noch aktiv und hatte noch nicht ausgelöst!!!');
        }

        // war abgesenkt?
        if (!$this->ReadAttributeBoolean('Reduction')) {
            $this->SendDebug('Restore', 'Kein Aktion  notwendig, da nicht ausgelöst!');
            return;
        }
        // HM 'WINDOW_STATE' set to <CLOSE>
        $radiator = $this->ReadPropertyInteger('Radiator1');
        if ($radiator != 0) {
            $ret = HM_WriteValueInteger($radiator, 'WINDOW_STATE', 0);
            $this->SendDebug('Restore', 'Heizkörper 1: #' . $radiator . ' Fensterstatus auf CLOSE setzen => ' . var_export($ret, true));
        }
        // HM 'WINDOW_STATE' set to <CLOSE>
        $radiator = $this->ReadPropertyInteger('Radiator2');
        if ($radiator != 0) {
            $ret = HM_WriteValueInteger($radiator, 'WINDOW_STATE', 0);
            $this->SendDebug('Restore', 'Heizkörper 2: #' . $radiator . ' Fensterstatus auf CLOSE setzen => ' . var_export($ret, true));
        }
        // Internal State
        $this->WriteAttributeBoolean('Reduction', false);
        // Send Message
        $this->SendMessage(false);
    }

    private function SendMessage($state)
    {
        $img = 'Window';
        $txt = '';
        $msg = 0;
        $typ = 4;
        // set the right parameter
        if ($state) {
            $img .= '-0';
            $txt = $this->Translate('Temperature is lowered!');
            $typ = 2;
        } else {
            $img .= '-100';
            $txt = $this->Translate('Temperature reduction cancelled!');
            $typ = 0;
        }
        // send message?
        $scriptId = $this->ReadPropertyInteger('ScriptMessage');
        if ($scriptId != 0) {
            $room = $this->ReadPropertyString('RoomName');
            $time = $this->ReadPropertyInteger('LifeTime');
            $time = $time * 60;
            // remove old message?
            if (!$state) {
                $msg = $this->ReadAttributeInteger('Message');
                if ($msg > 0) {
                    IPS_RunScriptWaitEx($scriptId, ['action' => 'remove', 'number' => $msg]);
                }
            }
            // send new message
            if (IPS_ScriptExists($scriptId)) {
                if ($time > 0) {
                    $msg = IPS_RunScriptWaitEx($scriptId, ['action' => 'add', 'text' => $room . ': ' . $txt, 'expires' => time() + $time, 'removable' => true, 'type' => $typ, 'image' => $img]);
                } else {
                    $msg = IPS_RunScriptWaitEx($scriptId, ['action' => 'add', 'text' => $room . ': ' . $txt, 'removable' => true, 'type' => $typ, 'image' => $img]);
                }
            }
            // bookmark message
            $this->WriteAttributeInteger('Message', $msg);
        }
    }

    private function InternalState()
    {
        $reduction = false;
        $sensors = 0;

        // Heizkörper Status
        $vid = $this->ReadPropertyInteger('Radiator1');
        if ($vid != 0) {
            $oid = @IPS_GetObjectIDByIdent('WINDOW_STATE', $vid);
            if ($oid !== false) {
                $reduction |= GetValue($oid);
            }
        }
        $vid = $this->ReadPropertyInteger('Radiator2');
        if ($vid != 0) {
            $oid = @IPS_GetObjectIDByIdent('WINDOW_STATE', $vid);
            if ($oid !== false) {
                $reduction |= GetValue($oid);
            }
        }
        // Kontakt Status
        $vid = $this->ReadPropertyInteger('StateVariable');
        if ($vid != 0) {
            $sensors = $sensors + (GetValue($vid) ? 1 : 0);
        }
        $vid = $this->ReadPropertyInteger('StateVariable2');
        if ($vid != 0) {
            $sensors = $sensors + (GetValue($vid) ? 2 : 0);
        }
        // deactivate all timer
        $this->SetTimerInterval('DelayTrigger', 0);
        $this->SetTimerInterval('RepeatTrigger', 0);
        $this->SetTimerInterval('SwitchTrigger', 0);
        // set sensor state and open/close state
        $this->WriteAttributeInteger('Sensors', $sensors);
        $this->WriteAttributeBoolean('Reduction', $reduction);
        $this->SendDebug('SetInternalState', 'Sensors: ' . $sensors . ' Reduction: ' . var_export($reduction, true), 0);
    }
}
