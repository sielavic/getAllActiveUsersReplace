public static function getAllActiveUsersReplace()
    {
        \Entity\Job::setUsersOnHoliday();
        $result = self::includes('Employee.position')->with('employee')->
        where('Employee.enabled', 1)->orderBy('Employee.surname')->get();
        if (!$result->isEmpty()) {

            foreach ($result as $r) {

                if ($r->on_holiday == 0) {
                    $r->fullNameWithPosition = $r->employee->getFioWithPositionName();
                } else if ($r->on_holiday == 1) {
                    $userReplacing = null;
                    $user_id_asking = \Entity\MacrotaskPeti::where('user_id_asking', $r->id)->where('agreed', 1)->where('user_id_replacing', '!=', 0)->orderBy('date_start')->get()[0];

                    $no_change_agreer = '';
                    $currentPeti = $r->getCurrentPeti();
                    if (!empty($currentPeti)) {
                        $current_date = date('Y-m-d', strtotime($currentPeti->date_end));
                        if ($current_date == date('Y-m-d')) {
                            $no_change_agreer = 1;
                        }
                    }

                    if ($user_id_asking->user_id_replacing != 0) {
                        $userReplacing = \Entity\User::find($user_id_asking->user_id_replacing);
                    }
                    if (!empty($userReplacing) && empty($no_change_agreer)) {
                        $r->fullNameWithPosition = $userReplacing->employee->getFioWithPositionName();
                        $r->replace_id = $user_id_asking->user_id_replacing;
                    } else if (!empty($no_change_agreer)) {
                        $r->fullNameWithPosition = $r->employee->getFioWithPositionName();
                    } else {
                        $r->fullNameWithPosition = 'нет заменяющего';
                    }

                }

            }
            return $result;
        } else {
            return false;
        }

    }
