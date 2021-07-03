<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * This is the model class for "Template".
 */
class Template extends Model
{
    const DEFAULT_TEMPLATE_PATH = '@webroot/templates';

    public static function getFileByName($catalog, $name)
    {
        $extensions = ['docx', 'xls'];
        $path = Yii::getAlias(self::DEFAULT_TEMPLATE_PATH);

        foreach ($extensions as $extension) {
            $file = sprintf('%s/%s/%s.%s', $path, $catalog, $name, $extension);
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }

    public static function getUserParameters($user)
    {
        return [
            'fullName' => $user->fullName,
            'shortName' => $user->shortName,
            'citizen' => $user->citizen,
            'birthdate' => date('d.m.Y', strtotime($user->birthdate)),
            'passportSerial' => mb_substr($user->passport, 0, 4),
            'passportNumber' => mb_substr($user->passport, 4),
            'passportDate' => date('d.m.Y', strtotime($user->passport_date)),
            'passportDepartment' => $user->passport_department,
            'itn' => $user->itn,
            'registration' => $user->registration,
            'residence' => !empty($user->residence) ? $user->residence : $user->registration,
            'phone' => $user->phone,
            'email' => $user->email,
            'currentDate' => date('d.m.Y'),
            'createdDate' => date('d.m.Y', strtotime($user->created_at)),
            'skills' => $user->skills,
            'recommender_info' => $user->recommender_info ? $user->recommender_info : 'Нет',
            'number' => $user->number,
        ];
    }

    public static function parseTemplate($parameters, $template)
    {
        foreach ($parameters as $name => $value) {
            $template = preg_replace('/\$\{' . $name . '\}/', $value, $template);
        }

        return $template;
    }
}
