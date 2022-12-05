<?php

declare(strict_types=1);

// Generell funktions
require_once __DIR__ . '/../libs/_traits.php';

// CLASS ContactSensor
class ContactSensor extends IPSModule
{
    use ProfileHelper;
    use DebugHelper;

    /**
     * Create.
     */
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        // Contact state variables
        $this->RegisterPropertyInteger('StateVariable', 0);
        $this->RegisterPropertyInteger('StateVariable2', 0);
        $this->RegisterPropertyInteger('StateVariable3', 0);
        $this->RegisterPropertyInteger('StateVariable4', 0);
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
        $this->RegisterPropertyInteger('DashboardMessage', 0);
        $this->RegisterPropertyInteger('DashboardTrigger', 3);
        $this->RegisterPropertyInteger('DashboardOpening', 0);
        $this->RegisterPropertyInteger('DashboardClosing', 0);
        $this->RegisterPropertyInteger('NotificationMessage', 0);
        $this->RegisterPropertyInteger('NotificationTrigger', 3);
        $this->RegisterPropertyString('RoomName', $this->Translate('Unknown'));
        $this->RegisterPropertyString('TextOpening', $this->Translate('%R: Temperature is lowered!'));
        $this->RegisterPropertyString('TextClosing', $this->Translate('%R: Temperature reduction cancelled!'));
        $this->RegisterPropertyInteger('InstanceWebfront', 0);
        $this->RegisterPropertyInteger('ScriptMessage', 0);
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

    /**
     * Destroy.
     */
    public function Destroy()
    {
        parent::Destroy();
    }

    /**
     * Apply Configuration Changes.
     */
    public function ApplyChanges()
    {
        if ($this->ReadPropertyInteger('StateVariable') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('StateVariable'), VM_UPDATE);
        }
        if ($this->ReadPropertyInteger('StateVariable2') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('StateVariable2'), VM_UPDATE);
        }
        if ($this->ReadPropertyInteger('StateVariable3') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('StateVariable3'), VM_UPDATE);
        }
        if ($this->ReadPropertyInteger('StateVariable4') != 0) {
            $this->UnregisterMessage($this->ReadPropertyInteger('StateVariable4'), VM_UPDATE);
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
        if (IPS_VariableExists($this->ReadPropertyInteger('StateVariable3'))) {
            $this->RegisterMessage($this->ReadPropertyInteger('StateVariable3'), VM_UPDATE);
        }
        if (IPS_VariableExists($this->ReadPropertyInteger('StateVariable3'))) {
            $this->RegisterMessage($this->ReadPropertyInteger('StateVariable3'), VM_UPDATE);
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
        // $this->SendDebug(__FUNCTION__, 'SenderId: '. $senderID . ' Data: ' . print_r($data, true), 0);
        switch ($message) {
            case VM_UPDATE:
                if ($senderID == $this->ReadPropertyInteger('StateVariable')) {
                    $this->SendDebug(__FUNCTION__, 'Kontaktsender 1: #' . $senderID . ', New: ' . $data[0] . ', Changed: ' . var_export($data[1], true) . ', Old: ' . $data[2], 0);
                    // state changes ?
                    if ($data[0] == 1 && $data[1] == true) { // state on 1, i.e. OPEN
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 1: State auf <OPEN> geschalten');
                        $this->Open(1);
                    } elseif ($data[0] == 0 && $data[1] == true) { // state on 0, i.e. CLOSE
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 1: State auf <CLOSE> geschalten');
                        $this->Close(1);
                    } else { // OnChange - no state changes
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 1: State unveraendert - keine Zustandsänderung');
                    }
                } elseif ($senderID == $this->ReadPropertyInteger('StateVariable2')) {
                    $this->SendDebug(__FUNCTION__, 'Kontaktsender 2: #' . $senderID . ', New: ' . $data[0] . ', Changed: ' . var_export($data[1], true) . ', Old: ' . $data[2], 0);
                    // state changes ?
                    if ($data[0] == 1 && $data[1] == true) { // state on 1, i.e. OPEN
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 2: State auf <OPEN> geschalten');
                        $this->Open(2);
                    } elseif ($data[0] == 0 && $data[1] == true) { // state on 0, i.e. CLOSE
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 2: State auf <CLOSE> geschalten');
                        $this->Close(2);
                    } else { //OnChange - no state changes
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 2: State unveraendert - keine Zustandsänderung');
                    }
                } elseif ($senderID == $this->ReadPropertyInteger('StateVariable3')) {
                    $this->SendDebug(__FUNCTION__, 'Kontaktsender 3: #' . $senderID . ', New: ' . $data[0] . ', Changed: ' . var_export($data[1], true) . ', Old: ' . $data[2], 0);
                    // state changes ?
                    if ($data[0] == 1 && $data[1] == true) { // state on 1, i.e. OPEN
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 3: State auf <OPEN> geschalten');
                        $this->Open(4);
                    } elseif ($data[0] == 0 && $data[1] == true) { // state on 0, i.e. CLOSE
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 3: State auf <CLOSE> geschalten');
                        $this->Close(4);
                    } else { // OnChange - no state changes
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 3: State unveraendert - keine Zustandsänderung');
                    }
                } elseif ($senderID == $this->ReadPropertyInteger('StateVariable4')) {
                    $this->SendDebug(__FUNCTION__, 'Kontaktsender 4: #' . $senderID . ', New: ' . $data[0] . ', Changed: ' . var_export($data[1], true) . ', Old: ' . $data[2], 0);
                    // state changes ?
                    if ($data[0] == 1 && $data[1] == true) { // state on 1, i.e. OPEN
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 4: State auf <OPEN> geschalten');
                        $this->Open(8);
                    } elseif ($data[0] == 0 && $data[1] == true) { // state on 0, i.e. CLOSE
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 4: State auf <CLOSE> geschalten');
                        $this->Close(8);
                    } else { // OnChange - no state changes
                        $this->SendDebug(__FUNCTION__, 'Kontaktsender 4: State unveraendert - keine Zustandsänderung');
                    }
                } else {
                    $this->SendDebug(__FUNCTION__, 'Kontaktsender: #' . $senderID . ' unbekannt!');
                }
                break;
        }
    }

    /**
     * RequestAction.
     *
     *  @param string $ident Ident.
     *  @param string $value Value.
     */
    public function RequestAction($ident, $value)
    {
        // Debug output
        $this->SendDebug(__FUNCTION__, $ident . ' Timer wurde ausgelöst!');
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

    /**
     * Open - Executes if state is OPEN.
     *
     * @* @param Integer $sensor Id of the triggered sensor
     */
    private function Open($sensor)
    {
        // (1) push sensor to process
        $sensors = $this->ReadAttributeInteger('Sensors');
        $sensors = $sensors ^ $sensor;
        $this->WriteAttributeInteger('Sensors', $sensors);
        // (2) check timer or active reduction
        $delay = $this->ReadPropertyInteger('Delay');
        if ((($delay > 0) && ($this->GetTimerInterval('DelayTrigger') != 0)) || $this->ReadAttributeBoolean('Reduction')) {
            $this->SendDebug(__FUNCTION__, 'Kontaktsender ' . $sensor . ' wollte auslösen, aber ein anderer Sensor war schneller!');
            return;
        }
        // (3) start action
        $this->SendDebug(__FUNCTION__, 'Kontaktsender ' . $sensor . ' hat Prozess ausgelöst!');
        if ($delay > 0) {
            $this->SetTimerInterval('DelayTrigger', 1000 * $delay);
        } else {
            $this->Decrease();
        }
    }

    /**
     * Close - Executes if state is CLOSE.
     *
     * @param int $sensor Id of the triggered sensor
     */
    private function Close($sensor)
    {
        // (1) pop sensor from process
        $sensors = $this->ReadAttributeInteger('Sensors');
        $sensors = $sensors ^ $sensor;
        $this->WriteAttributeInteger('Sensors', $sensors);
        // (2) check if some sensor still in process
        if ($sensors > 0) {
            $this->SendDebug(__FUNCTION__, 'Kontaktsender ' . $sensor . ' wollte aufheben, aber ein anderer Sensor ist noch offen!');
            return;
        }
        // (3) end action
        $this->SendDebug(__FUNCTION__, 'Kontaktsender ' . $sensor . ' hat Prozess beendet!');
        $this->Restore();
    }

    /**
     * Decrease - decrease th heater temperature.
     */
    private function Decrease()
    {
        $this->SendDebug(__FUNCTION__, 'Funktion wurde aufgerufen!');
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
                    $this->SendDebug(__FUNCTION__, 'Ventilpostionscheck ist aktiv und traf zu!');
                    $condition = false;
                }
            } else {
                $this->SendDebug(__FUNCTION__, 'Ventilpostionscheck ist aktiv aber keine Positionsvariable hinterlegt!');
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

    /**
     * Restore - set heater back to his programm temperature.
     */
    private function Restore()
    {
        $this->SendDebug(__FUNCTION__, 'Funktion wurde aufgerufen!');
        // Active delay timer?
        if ($this->GetTimerInterval('DelayTrigger') > 0) {
            // Timer deactivate
            $this->SetTimerInterval('DelayTrigger', 0);
            $this->SendDebug(__FUNCTION__, 'Ein Verzögerungs-Timer hatte noch nicht ausgelöst!!!');
        }
        // Active repeat timer?
        if ($this->GetTimerInterval('RepeatTrigger') > 0) {
            // Timer deactivate
            $this->SetTimerInterval('RepeatTrigger', 0);
            $this->SendDebug(__FUNCTION__, 'Ein Wiederholungs-Timer war noch aktiv!!!');
        }
        // Active switch back timer?
        if ($this->GetTimerInterval('SwitchTrigger') > 0) {
            // Timer deactivate
            $this->SetTimerInterval('SwitchTrigger', 0);
            $this->SendDebug(__FUNCTION__, 'Ein Aufhebungs-Timer war noch aktiv und hatte noch nicht ausgelöst!!!');
        }

        // was reducted?
        if (!$this->ReadAttributeBoolean('Reduction')) {
            $this->SendDebug(__FUNCTION__, 'Kein Aktion  notwendig, da nicht ausgelöst!');
            return;
        }
        // HM 'WINDOW_STATE' set to <CLOSE>
        $radiator = $this->ReadPropertyInteger('Radiator1');
        if ($radiator != 0) {
            $ret = @HM_WriteValueInteger($radiator, 'WINDOW_STATE', 0);
            if ($ret === false) {
                $this->LogMessage('Error writing WINDOW_STATE #1', KL_ERROR);
            }
            $this->SendDebug(__FUNCTION__, 'Heizkörper 1: #' . $radiator . ' Fensterstatus auf CLOSE setzen => ' . var_export($ret, true));
        }
        // HM 'WINDOW_STATE' set to <CLOSE>
        $radiator = $this->ReadPropertyInteger('Radiator2');
        if ($radiator != 0) {
            $ret = @HM_WriteValueInteger($radiator, 'WINDOW_STATE', 0);
            if ($ret === false) {
                $this->LogMessage('Error writing WINDOW_STATE #2', KL_ERROR);
            }
            $this->SendDebug(__FUNCTION__, 'Heizkörper 2: #' . $radiator . ' Fensterstatus auf CLOSE setzen => ' . var_export($ret, true));
        }
        // Internal State
        $this->WriteAttributeBoolean('Reduction', false);
        // Send Message
        $this->SendMessage(false);
    }

    /**
     * SendMessage - if setuped. its send a message to indicate the state changes
     *
     * @param bool contact state (true is open | false is close).
     */
    private function SendMessage($state)
    {
        $isDashboard = $this->ReadPropertyInteger('DashboardMessage');
        $isNotify = $this->ReadPropertyInteger('NotificationMessage');
        // Check output
        if (!$isDashboard && !$isNotify) {
            // nothing to do
            return;
        }
        // trigger & duration
        $triggerDashboard = $this->ReadPropertyInteger('DashboardTrigger');
        $openingDashboard = $this->ReadPropertyInteger('DashboardOpening');
        $closingDashboard = $this->ReadPropertyInteger('DashboardClosing');
        $triggerNotify = $this->ReadPropertyInteger('NotificationTrigger');
        // text formates
        $open = $this->ReadPropertyString('TextOpening');
        $close = $this->ReadPropertyString('TextClosing');
        // webfront id & message script
        $webfront = $this->ReadPropertyInteger('InstanceWebfront');
        $msgscript = $this->ReadPropertyInteger('ScriptMessage');
        // specifier
        $value = [];
        $value['ROOM'] = $this->ReadPropertyString('RoomName');
        $value['TYPE'] = ($state ? $this->Translate('OPEN') : $this->Translate('CLOSE'));
        $value['DATE'] = (date('d.m.Y', time()));
        $value['TIME'] = (date('H:i:s', time()));
        // build message
        $img = 'Window';
        $txt = '';
        $msg = 0;
        $typ = 4;
        $time = 0;
        $sdb = false;
        $swf = false;
        // set the right parameter
        if ($state) {
            $img .= '-0';
            $txt = $this->FormatMessage($value, $open);
            $typ = 2;
            $sdb = ($triggerDashboard & 1);
            $swf = ($triggerNotify & 1);
            $time = $openingDashboard * 60;
        } else {
            $img .= '-100';
            $txt = $this->FormatMessage($value, $close);
            $typ = 0;
            $sdb = ($triggerDashboard & 2);
            $swf = ($triggerNotify & 2);
            $time = $closingDashboard * 60;
        }
        // debug
        $this->SendDebug(__FUNCTION__, 'Image:' . $img . ', Text: ' . $txt . ', SDB:' . $sdb . ', SWF:' . $swf . ', Time:' . $time);
        // send notify?
        if ($isNotify && $webfront != 0 && $swf) {
            WFC_PushNotification($webfront, $this->Translate('Contact Sensor'), $txt, $img, 0);
        }
        // send message?
        if ($isDashboard && $msgscript != 0 && $sdb) {
            // remove old message?
            if (!$state) {
                $msg = $this->ReadAttributeInteger('Message');
                if ($msg > 0) {
                    IPS_RunScriptWaitEx($msgscript, ['action' => 'remove', 'number' => $msg]);
                }
            }
            // send new message
            if (IPS_ScriptExists($msgscript)) {
                if ($time > 0) {
                    $msg = IPS_RunScriptWaitEx($msgscript, ['action' => 'add', 'text' => $txt, 'expires' => time() + $time, 'removable' => true, 'type' => $typ, 'image' => $img]);
                } else {
                    $msg = IPS_RunScriptWaitEx($msgscript, ['action' => 'add', 'text' => $txt, 'removable' => true, 'type' => $typ, 'image' => $img]);
                }
            }
            // bookmark message
            $this->WriteAttributeInteger('Message', $msg);
        }
    }

    /**
     * Format a given array to a string.
     *
     * @param array $value Weather warning data
     * @param string $format Format string
     */
    private function FormatMessage(array $value, $format)
    {
        $output = str_replace('%R', $value['ROOM'], $format);
        $output = str_replace('%M', $value['TYPE'], $output);
        $output = str_replace('%D', $value['DATE'], $output);
        $output = str_replace('%T', $value['TIME'], $output);
        return $output;
    }

    /**
     * Internal state
     */
    private function InternalState()
    {
        $reduction = false;
        $sensors = 0;

        // heater state
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
        // contact state
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
        $this->SendDebug(__FUNCTION__, 'Sensors: ' . $sensors . ' Reduction: ' . var_export($reduction, true), 0);
    }
}
