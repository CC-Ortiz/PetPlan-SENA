<?php
return ['defaults'=>['guard'=>'web','passwords'=>'users'],'guards'=>['web'=>['driver'=>'session','provider'=>'users']],'providers'=>['users'=>['driver'=>'database','table'=>'usuario']],'passwords'=>[],'password_timeout'=>10800];
