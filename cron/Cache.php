<?php
/**
 * Clear cache cron
 * @package YetiForce.Cron
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Michał Lorencik <m.lorencik.com>
 */
$limitView = AppConfig::performance('BROWSING_HISTORY_VIEW_LIMIT');
$sql = "SELECT COUNT(id) AS record_count, id_user, (SELECT sub.view_date FROM u_yf_browsinghistory as sub WHERE id_user=sub.id_user ORDER BY sub.view_date LIMIT $limitView,1) AS view_date FROM u_yf_browsinghistory GROUP BY id_user HAVING record_count > $limitView";

$result = (new \App\Db\Query())->createCommand()->setSql($sql)->queryAll();
foreach ($result as $record) {
	(new \App\Db\Query())->createCommand()
		->delete('u_yf_browsinghistory', 'id_user=:idUser and view_date < :viewDate', ['idUser' => $record['id_user'], 'viewDate' => $record['view_date']])
		->execute();
}
