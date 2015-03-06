<?php
namespace cascade\components\helpers;

use infinite\caching\Cacher;

/**
 * Gravatar [[@doctodo class_description:cascade\components\helpers\Gravatar]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Gravatar extends \emberlabs\gravatarlib\Gravatar
{
    /**
     * [[@doctodo method_description:test]].
     *
     * @param [[@doctodo param_type:email]] $email      [[@doctodo param_description:email]]
     * @param boolean                       $hash_email [[@doctodo param_description:hash_email]] [optional]
     *
     * @return [[@doctodo return_type:test]] [[@doctodo return_description:test]]
     */
    public function test($email, $hash_email = true)
    {
        $original = $this->getDefaultImage();
        $this->setDefaultImage(404);
        $url = htmlspecialchars_decode($this->get($email, $hash_email));
        $this->setDefaultImage($original);
        $cacheKey = ['testGravatar', $url];
        $cache = Cacher::get($cacheKey);
        if ($cache) {
            return $cache;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        $data = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!$data || $httpCode !== 200) {
            $result = false;
        } else {
            $result = true;
        }
        Cacher::set($cacheKey, $result, 3600);

        return $result;
    }

    /**
     * [[@doctodo method_description:fetch]].
     *
     * @param [[@doctodo param_type:email]] $email      [[@doctodo param_description:email]]
     * @param boolean                       $hash_email [[@doctodo param_description:hash_email]] [optional]
     *
     * @return [[@doctodo return_type:fetch]] [[@doctodo return_description:fetch]]
     */
    public function fetch($email, $hash_email = true)
    {
        $url = $this->get($email, $hash_email);
        $cacheKey = md5('gravatar-' . $url);
        $cache = Yii::$app->fileCache->get($cacheKey);
        if ($cache) {
            return unserialize($cache);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        $data = curl_exec($curl);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!$data || $httpCode !== 200) {
            $data = false;
        }
        Yii::$app->fileCache->set($cacheKey, serialize($data), 3600);

        return $data;
    }
}
