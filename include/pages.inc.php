<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function index_page() {
    
}

function page_view() {
    return view();
}

function page_biblio() {
    global $LNG, $cfg;

    if (
            isset($_POST['num_id_show']) &&
            ($cfg['max_identify_items'] != $_POST['num_id_show'])
    ) {
        $cfg['max_identify_items'] = $_POST['num_id_show'];
        setPrefsItem('max_identify_items', $cfg['max_identify_items']);
    }
    ($cfg['max_identify_items'] == 0) ? $max_id_sel_0 = 'selected' : $max_id_sel_0 = '';
    ($cfg['max_identify_items'] == 5) ? $max_id_sel_5 = 'selected' : $max_id_sel_5 = '';
    ($cfg['max_identify_items'] == 10) ? $max_id_sel_10 = 'selected' : $max_id_sel_10 = '';
    ($cfg['max_identify_items'] == 20) ? $max_id_sel_20 = 'selected' : $max_id_sel_20 = '';
    ($cfg['max_identify_items'] == 50) ? $max_id_sel_50 = 'selected' : $max_id_sel_50 = '';

    $tdata['max_id_sel_0'] = $max_id_sel_0;
    $tdata['max_id_sel_5'] = $max_id_sel_5;
    $tdata['max_id_sel_10'] = $max_id_sel_10;
    $tdata['max_id_sel_20'] = $max_id_sel_20;
    $tdata['max_id_sel_50'] = $max_id_sel_50;

    /* ROWS */
    $max_rows_sel_none = '';

    if (isset($_POST['num_rows_results'])) {
        if ($_POST['num_rows_results'] == $LNG['L_DEFAULT']) {
            $max_rows_sel_none = 'selected';
        } else {
            $cfg['tresults_rows'] = $_POST['num_rows_results'];
            setPrefsItem('tresults_rows', $cfg['tresults_rows']);
        }
    }

    ($cfg['tresults_rows'] == 1) ? $tdata['max_rows_sel_1'] = 'selected' : $tdata['max_rows_sel_1'] = '';
    ($cfg['tresults_rows'] == 2) ? $tdata['max_rows_sel_2'] = 'selected' : $tdata['max_rows_sel_2'] = '';
    ($cfg['tresults_rows'] == 4) ? $tdata['max_rows_sel_4'] = 'selected' : $tdata['max_rows_sel_4'] = '';
    ($cfg['tresults_rows'] == 6) ? $tdata['max_rows_sel_6'] = 'selected' : $tdata['max_rows_sel_6'] = '';
    ($cfg['tresults_rows'] == 8) ? $tdata['max_rows_sel_8'] = 'selected' : $tdata['max_rows_sel_8'] = '';
    ($cfg['tresults_rows'] == 10) ? $tdata['max_rows_sel_10'] = 'selected' : $tdata['max_rows_sel_10'] = '';
    $tdata['max_rows_sel_none'] = $max_rows_sel_none;

    /* COLUMNS */

    $max_columns_sel_none = '';

    if (isset($_POST['num_columns_results'])) {
        if ($_POST['num_columns_results'] == $LNG['L_DEFAULT']) {
            $max_columns_sel_none = 'selected';
        } else {
            $cfg['tresults_columns'] = $_POST['num_columns_results'];
            setPrefsItem('tresults_columns', $cfg['tresults_columns']);
        }
    }

    ($cfg['tresults_columns'] == 1) ? $tdata['max_columns_sel_1'] = 'selected' : $tdata['max_columns_sel_1'] = '';
    ($cfg['tresults_columns'] == 2) ? $tdata['max_columns_sel_2'] = 'selected' : $tdata['max_columns_sel_2'] = '';
    ($cfg['tresults_columns'] == 4) ? $tdata['max_columns_sel_4'] = 'selected' : $tdata['max_columns_sel_4'] = '';
    ($cfg['tresults_columns'] == 6) ? $tdata['max_columns_sel_6'] = 'selected' : $tdata['max_columns_sel_6'] = '';
    ($cfg['tresults_columns'] == 8) ? $tdata['max_columns_sel_8'] = 'selected' : $tdata['max_columns_sel_8'] = '';
    ($cfg['tresults_columns'] == 10) ? $tdata['max_columns_sel_10'] = 'selected' : $tdata['max_columns_sel_10'] = '';
    $tdata['max_columns_sel_none'] = $max_columns_sel_none;
    /* FIN */

    $page = getTpl('library_options', array_merge($tdata, $LNG));

    $page .= show_my_movies();
    $page .= show_my_shows();

    return $page;
}

function page_news() {
    global $cfg;

    foreach ($cfg['jackett_indexers'] as $indexer) {
        $results = jackett_search_movies('', $indexer);
        ($results) ? $movies_res[$indexer] = $results : null;
        $results = null;
        $results = jackett_search_shows('', $indexer);
        $results ? $shows_res[$indexer] = $results : null;
    }

    $res_movies_db = jackett_prep_movies($movies_res);
    $res_shows_db = jackett_prep_shows($shows_res);

    /* BUILD PAGE */

    $page_news = '';

    if (!empty($res_movies_db)) {
        $topt['search_type'] = 'movies';
        $page_news_movies = buildTable('L_MOVIES', $res_movies_db, $topt);
        $page_news .= $page_news_movies;
    }

    if (!empty($res_shows_db)) {
        $topt['search_type'] = 'shows';
        $page_news_shows = buildTable('L_SHOWS', $res_shows_db, $topt);
        $page_news .= $page_news_shows;
    }

    return $page_news;
}

function page_tmdb() {
    global $LNG;

    (!empty($_GET['search_movies'])) ? $tdata['search_movies_word'] = $_GET['search_movies'] : $tdata['search_movies_word'] = '';
    (!empty($_GET['search_shows'])) ? $tdata['search_shows_word'] = $_GET['search_shows'] : $tdata['search_shows_word'] = '';

    $page = getTpl('page_tmdb', array_merge($LNG, $tdata));

    if (!empty($_GET['search_movies'])) {
        $movies = db_search_movies(trim($_GET['search_movies']));
        $topt['search_type'] = 'movies';
        !empty($movies) ? $page .= buildTable('L_DB', $movies, $topt) : null;
    }

    if (!empty($_GET['search_shows'])) {
        $shows = db_search_shows(trim($_GET['search_shows']));
        $topt['search_type'] = 'shows';
        !empty($shows) ? $page .= buildTable('L_DB', $shows, $topt) : null;
    }

    return $page;
}

function page_torrents() {
    global $LNG;

    (!empty($_GET['search_movies_torrents'])) ? $tdata['search_movies_word'] = $_GET['search_movies_torrents'] : $tdata['search_movies_word'] = '';
    (!empty($_GET['search_shows_torrents'])) ? $tdata['search_shows_word'] = $_GET['search_shows_torrents'] : $tdata['search_shows_word'] = '';

    $page = getTpl('page_torrents', array_merge($tdata, $LNG));

    if (!empty($_GET['search_shows_torrents'])) {
        $torrent_results = search_shows_torrents(trim($_GET['search_shows_torrents']), 'L_TORRENT');

        if ($torrent_results !== false) {
            $page .= $torrent_results;
        } else {
            $error_msg['title'] = $LNG['L_ERROR'] . ':' . $LNG['L_TORRENT'];
            $error_msg['body'] = $LNG['L_NOTHING_FOUND'];
            $page .= error_box($error_msg);
        }
    }
    if (!empty($_GET['search_movies_torrents'])) {
        $page .= search_movie_torrents(trim($_GET['search_movies_torrents']), 'L_TORRENT');
    }

    return $page;
}

function page_wanted() {
    global $LNG, $cfg, $db;

    $want = [];
    $want['SELECTED_MOVIES'] = '';
    $want['SELECTED_SHOWS'] = '';
    $want['page'] = $_GET['page'];
    $item = [];

    
    isset($_GET['id']) ? $wanted_id = $_GET['id'] : $wanted_id = false;
    isset($_GET['type']) ? $wanted_type = $_GET['type'] : $wanted_type = false;

    
    if($wanted_id !== false && $wanted_type !== false) {
        if($wanted_type == 'movies') {
            $item = wanted_movies($wanted_id);
        } else if ($wanted_type == 'shows' ) {
            $item = wanted_shows($wanted_id);
        }     
    }

    $wanted_list = $db->getTableData('wanted');
    if (!empty($wanted_list)) {
        $wanted_list_data = '<h2>' . $LNG['L_WANTED'] . '</h2>';
        $wanted_list_data .= '<div class="wanted_list_container">';
        foreach ($wanted_list as $wanted_item) {
            $wanted_list_data .= '<div class="wanted_list_row">';
            $wanted_list_data .= '<span class="tag_id">' . $wanted_item['id'] . '</span>';
            $wanted_list_data .= '<span class="tag_title">' . $wanted_item['title'] . '</span>';
            $wanted_list_data .= '<span class="tag_type">' . $wanted_item['type'] . '</span>';
            $wanted_list_data .= '<span class="tag_day">' . $LNG[$wanted_item['day_check']] . '</span>';
            foreach ($cfg['TORRENT_QUALITYS_PREFS'] as $quality) {
                $wanted_list_data .= '<span class="tag_quality">' . $quality . '</span>';
            }
            foreach ($cfg['TORRENT_IGNORES_PREFS'] as $ignores) {
                $wanted_list_data .= '<span class="tag_ignore">' . $ignores . '</span>';
            }
            $wanted_list_data .= '<span class="tag_added">' . $LNG['L_ADDED'] . ':' . date('d-m-y', $wanted_item['added']) . '</span>';
            $wanted_list_data .= '<span class="tag_id">TMDB:' . $wanted_item['themoviedb_id'] . '</span>';
            $wanted_list_data .= '</div>';
        }
        $wanted_list_data .= '</div>';
        $want['wanted_list'] = $wanted_list_data;
    }
    return getTpl('wanted', array_merge($item, $want, $LNG, $cfg));
}
