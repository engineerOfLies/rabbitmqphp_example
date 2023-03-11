<?php
switch ($action) {
	case 'bookmark_posting':
		if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
			$user_id = intval($_SESSION[$settings['session_prefix'] . 'user_id']);
			$result = @mysqli_query($connid, "SELECT TRUE AS 'exists' FROM " . $db_settings['bookmark_table'] . " WHERE user_id = " . intval($user_id) . " AND posting_id = " . intval($_GET['bookmark']) . " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			$exists = mysqli_fetch_row($result);
			mysqli_free_result($result);
			if (isset($exists) && intval($exists) == 1) {
				$result = @mysqli_query($connid, "SELECT `id` FROM " . $db_settings['bookmark_table'] . " WHERE `user_id`= " . intval($user_id) . " AND `posting_id` = " . intval($_GET['bookmark']) . " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				if(mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_array($result);
					deleteBookmark($row['id']);
				}
				mysqli_free_result($result);

			} 
			else {
				addBookmark($user_id, $_GET['bookmark']);
			}
			header("location: index.php?mode=" . $back . "&id=" . intval($_GET['bookmark']));
		}
		break;
	case 'mark':
		if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) {
			$id = intval($_REQUEST['mark']);
			$mark_result = @mysqli_query($connid, "SELECT marked FROM " . $db_settings['forum_table'] . " WHERE id=" . $id . " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			$field = mysqli_fetch_array($mark_result);
			mysqli_free_result($mark_result);
			if ($field['marked'] == 0)
				$marked = 1;
			else
				$marked = 0;
			@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time = time, last_reply = last_reply, edited = edited, marked = " . $marked . " WHERE id = " . $id);

			if (isset($_POST['method']) && $_POST['method'] == 'ajax') {
				header('Content-Type: application/xml; charset=UTF-8');
				echo '<?xml version="1.0"?>';
?><mark><action><?php
				echo $marked;
?></action></mark><?php
			} else {
				if (isset($_GET['back']))
					header('Location: index.php?id=' . intval($_GET['back']));
				else
					header('Location: index.php?mode=index');
			}
			exit;
		} else {
			header('Location: index.php?mode=index');
			exit;
		}
		break;
	case 'posting':
		if ($settings['forum_readonly'] == 1) {
			$subnav_link = array(
				'mode' => 'index',
				'name' => 'forum_index_link',
				'title' => 'forum_index_link_title'
			);
			$smarty->assign("subnav_link", $subnav_link);
			$smarty->assign('no_authorisation', 'no_auth_readonly');
			$smarty->assign('subtemplate', 'posting.inc.tpl');
		} elseif ($settings['entries_by_users_only'] != 0 && empty($_SESSION[$settings['session_prefix'] . 'user_id'])) {
			// is user authorisized to post messages?
			$back = '&back=posting';
			if (isset($id) && $id > 0)
				$back .= '&id=' . $id;
			header('Location: index.php?mode=login&login_message=registered_users_only' . $back);
			exit;
		} else {
			// set timestamp for SPAM protection
			setReceiptTimestamp();
			if ($id == 0) {
				// new posting
				$link_name  = 'back_to_index_link';
				$link_title = 'back_to_index_link_title';
				$smarty->assign('p_category', $category);
				$subnav_link = array(
					'mode' => $back,
					'id' => $id,
					'title' => $link_title,
					'name' => $link_name
				);
			} else {
				// reply
				// get data of message:
				$result = mysqli_query($connid, "SELECT tid, pid, name, user_name, " . $db_settings['forum_table'] . ".user_id, subject, category, text, locked
				FROM " . $db_settings['forum_table'] . "
				LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = " . $db_settings['forum_table'] . ".user_id
				WHERE id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) == 1) {
					$field = mysqli_fetch_array($result);
					mysqli_free_result($result);

					if ($field['locked'] > 0) {
						$smarty->assign('no_authorisation', 'posting_locked_no_reply');
					} else {
						$smarty->assign('subject', htmlspecialchars($field['subject']));
						$smarty->assign('p_category', $field['category']);
						$text = $field['text'];
						$text = quote_reply($text);
						$smarty->assign('text', htmlspecialchars($text));
						$smarty->assign('quote', true);
					}

					$link_name  = 'back_to_entry_link';
					$link_title = 'back_to_entry_link_title';
					if ($field['user_id'] > 0) {
						if (!$field['user_name'])
							$smarty->assign('name_repl_subnav', $lang['unknown_user']);
						else
							$smarty->assign('name_repl_subnav', htmlspecialchars($field['user_name']));
					} else
						$smarty->assign('name_repl_subnav', htmlspecialchars($field['name']));

					if (empty($back) || isset($back) && $back == 'entry') {
						$subnav_link = array(
							'id' => $id,
							'title' => $link_title,
							'name' => $link_name
						);
					} elseif (isset($back) && $back == 'index') {
						$subnav_link = array(
							'mode' => 'index',
							'name' => 'thread_entry_back_link',
							'title' => 'thread_entry_back_title'
						);
					} else {
						$subnav_link = array(
							'mode' => $back,
							'id' => $id,
							'title' => $link_title,
							'name' => $link_name
						);
					}
				} else {
					$subnav_link = array(
						'mode' => $back,
						'title' => 'forum_index_link_title',
						'name' => 'forum_index_link'
					);
					$smarty->assign('no_authorisation', 'error_posting_unavailable');
				}
			}

			if ($settings['remember_userdata'] && isset($_COOKIE[$settings['session_prefix'] . 'userdata'])) {
				$cookie_parts = explode("|", $_COOKIE[$settings['session_prefix'] . 'userdata']);
				$smarty->assign('name', htmlspecialchars(urldecode($cookie_parts[0])));
				if (isset($cookie_parts[1]))
					$smarty->assign('email', htmlspecialchars(urldecode($cookie_parts[1])));
				if (isset($cookie_parts[2]))
					$smarty->assign('hp', htmlspecialchars(urldecode($cookie_parts[2])));
				if (isset($cookie_parts[3]))
					$smarty->assign('location', htmlspecialchars(urldecode($cookie_parts[3])));
				$smarty->assign('setcookie', 1);
				$smarty->assign('cookie', true);
			}

			if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
				list($signature) = @mysqli_fetch_row(mysqli_query($connid, "SELECT signature FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($_SESSION[$settings['session_prefix'] . 'user_id'])));
				if (!empty($signature)) {
					$smarty->assign('signature', true);
					$smarty->assign('show_signature', 1);
				}
			}

			$smarty->assign("uniqid", uniqid(''));
			$smarty->assign('posting_mode', 0);
			$smarty->assign('id', intval($id));
			$smarty->assign('back', htmlspecialchars($back));
			$smarty->assign('action', htmlspecialchars($action));
			$smarty->assign('fld_user_name', $fname_user);
			$smarty->assign('fld_user_email', $fname_email);
			$smarty->assign('fld_phone', $fname_phone);
			$smarty->assign('fld_repeat_email', $fname_repemail);
			$smarty->assign('fld_hp', $fname_hp);
			$smarty->assign('fld_location', $fname_loc);
			$smarty->assign('fld_subject', $fname_subject);
			if ($settings['terms_of_use_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
				$smarty->assign('terms_of_use_agreement', true);
			if ($settings['data_privacy_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
				$smarty->assign('data_privacy_agreement', true);

			if (isset($_SESSION[$settings['session_prefix'] . 'user_id']) || $settings['email_notification_unregistered'] == 1)
				$smarty->assign('provide_email_notification', true);
			if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0 && (empty($id) || $posting_mode == 1 && $pid == 0))
				$smarty->assign('provide_sticky', true);

			$smarty->assign("subnav_link", $subnav_link);
			$smarty->assign('subtemplate', 'posting.inc.tpl');
		}
		break;
	case 'posting_submitted':
		if ($settings['forum_readonly'] == 1) {
			$subnav_link = array(
				'mode' => 'index',
				'name' => 'forum_index_link',
				'title' => 'forum_index_link_title'
			);
			$smarty->assign("subnav_link", $subnav_link);
			$smarty->assign('no_authorisation', 'no_auth_readonly');
			$smarty->assign('subtemplate', 'posting.inc.tpl');
		} 
		elseif ($settings['entries_by_users_only'] != 0 && empty($_SESSION[$settings['session_prefix'] . 'user_name']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
			if (isset($_POST['text'])) {
				$smarty->assign('no_authorisation', 'no_auth_session_expired');
				$smarty->assign('text', htmlspecialchars($_POST['text']));
			} 
			else {
				$smarty->assign('no_authorisation', 'no_auth_post_reg_users');
			}
			$smarty->assign('subtemplate', 'posting.inc.tpl');
		}
		// Honeypot - using 'default' error behavior; $_POST['name']) indicates a form for non-registered users
		elseif (isset($_POST[$fname_user]) && (!isset($_POST[$fname_repemail]) || !empty($_POST[$fname_repemail]) || !isset($_POST[$fname_phone]) || !empty($_POST[$fname_phone]))) {
			$smarty->assign('no_authorisation', 'no_auth_readonly');
			$smarty->assign('subtemplate', 'posting.inc.tpl');
		}
		else {
			if (!isset($_POST['preview'])) // ignore preview mode
				setReceiptTimestamp();

			if (isset($_POST['posting_mode']))
				$posting_mode = intval($_POST['posting_mode']);
			else
				$posting_mode = 0;

			// clear edit keys
			if ($settings['user_edit'] == 2 && $settings['edit_max_time_period'] > 0) {
				@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time = time, last_reply = last_reply, edited = edited, edit_key = '' WHERE time < (NOW() - INTERVAL " . intval($settings['edit_max_time_period']) . " MINUTE)");
			}

			// import, trim and complete data:
			$uniqid             = isset($_POST['uniqid']) ? trim($_POST['uniqid']) : '';
			$pid                = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
			$subject            = isset($_POST[$fname_subject]) ? trim($_POST[$fname_subject]) : '';
			$text               = isset($_POST['text']) ? trim($_POST['text']) : '';
			$email_notification = isset($_POST['email_notification']) && intval($_POST['email_notification'] == 1) ? 1 : 0;
			$email              = isset($_POST[$fname_email]) ? trim($_POST[$fname_email]) : '';
			$hp                 = isset($_POST[$fname_hp]) ? trim($_POST[$fname_hp]) : '';
			$location           = isset($_POST[$fname_loc]) ? trim($_POST[$fname_loc]) : '';
			$name               = isset($_POST[$fname_user]) ? trim($_POST[$fname_user]) : '';
			if (empty($_SESSION[$settings['session_prefix'] . 'user_id'])) {
				$setcookie          = isset($_POST['setcookie']) && intval($_POST['setcookie'] == 1) ? 1 : 0;
				$terms_of_use_agree = isset($_POST['terms_of_use_agree']) && intval($_POST['terms_of_use_agree'] == 1) ? 1 : 0;
				$data_privacy_agree = isset($_POST['data_privacy_statement_agree']) && intval($_POST['data_privacy_statement_agree'] == 1) ? 1 : 0;
			} else {
				$show_signature = isset($_POST['show_signature']) && intval($_POST['show_signature'] == 1) ? 1 : 0;
			}
			if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0 && isset($_POST['sticky']) && in_array(intval($_POST['sticky']), array(0, 1, 2))) {
				$sticky = intval($_POST['sticky']);
			} else {
				$sticky = 0;
			}
			if ($id != 0 && $posting_mode == 0 || $posting_mode == 1 && $pid > 0) {
				// get category of parent posting:
				$c_result = @mysqli_query($connid, "SELECT category FROM " . $db_settings['forum_table'] . " WHERE id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));
				$c_data     = mysqli_fetch_array($c_result);
				$p_category = $c_data['category'];
				mysqli_free_result($c_result);
			}
			// end trim and complete data

			if ($posting_mode == 0) {
				// new message
				if (empty($uniqid)) {
					$errors[] = 'error_invalid_form';
				} else {
					// double entry?
					list($double_entry_count) = @mysqli_fetch_row(@mysqli_query($connid, "SELECT COUNT(*) FROM " . $db_settings['forum_table'] . " WHERE uniqid = '" . mysqli_real_escape_string($connid, $uniqid) . "' AND time > (NOW()-INTERVAL 1 HOUR)"));
					if ($double_entry_count > 0) {
						header("Location: index.php?mode=index");
						exit;
					}
				}

				if (empty($errors)) {
					// check form session and time used to complete the form:
					//if (!isset($_POST['preview']) && empty($_SESSION[$settings['session_prefix'] . 'user_id'])) {  // ignore preview mode AND reg. users
					if (!isset($_POST['preview'])) { // ignore preview mode
						if (!isset($_SESSION[$settings['session_prefix'] . 'receipt_timestamp_difference']) || intval($_SESSION[$settings['session_prefix'] . 'receipt_timestamp_difference']) <= 0)
							$errors[] = 'error_invalid_form';
						else {
							if ($_SESSION[$settings['session_prefix'] . 'receipt_timestamp_difference'] < $settings['min_posting_time'])
								$errors[] = 'error_form_sent_too_fast';
							elseif ($_SESSION[$settings['session_prefix'] . 'receipt_timestamp_difference'] > $settings['max_posting_time'])
								$errors[] = 'error_form_sent_too_slow';
						}
					}
				}

				if (empty($errors)) {
					// flood prevention:
					if (empty($_SESSION[$settings['session_prefix'] . 'user_id']) && isset($_POST['save_entry'])) {
						list($flood_count) = @mysqli_fetch_row(@mysqli_query($connid, "SELECT COUNT(*) FROM " . $db_settings['forum_table'] . " WHERE user_id = 0 AND ip = '" . mysqli_real_escape_string($connid, $_SERVER['REMOTE_ADDR']) . "' AND time > (NOW()-INTERVAL " . $settings['flood_prevention_minutes'] . " MINUTE)"));
						if ($flood_count > 0) {
							$smarty->assign('minutes', $settings['flood_prevention_minutes']);
							$errors[] = 'error_repeated_posting';
						}
					}
				}

				// is it a registered user?
				if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
					$user_id = $_SESSION[$settings['session_prefix'] . 'user_id'];
					$name    = $_SESSION[$settings['session_prefix'] . 'user_name'];
				} else {
					$user_id        = 0;
					$show_signature = 0;
				}

				// get thread ID if reply:
				if ($id != 0) {
					$tid_result = @mysqli_query($connid, "SELECT tid, name, user_name, " . $db_settings['forum_table'] . ".user_id, category, locked
					FROM " . $db_settings['forum_table'] . "
					LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = " . $db_settings['forum_table'] . ".user_id
					WHERE id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));
					if (mysqli_num_rows($tid_result) != 1) {
						$errors[] = 'error_posting_unavailable';
					} else {
						$field = mysqli_fetch_array($tid_result);
						mysqli_free_result($tid_result);
						if ($field['locked'] != 0)
							$errors[] = 'posting_locked_no_reply';
						if ($field['category'] != 0 && !in_array($field['category'], $category_ids))
							$errors[] = 'error_invalid_category';
						$thread = $field['tid'];
						if ($field['user_id'] > 0) {
							if (!$field['user_name'])
								$smarty->assign('name_repl_subnav', $lang['unknown_user']);
							else
								$smarty->assign('name_repl_subnav', htmlspecialchars($field['user_name']));
						} else
							$smarty->assign('name_repl_subnav', htmlspecialchars($field['name']));
						$link_name  = 'back_to_entry_link';
						$link_title = 'back_to_entry_link_title';
					}
				} else {
					$thread     = 0;
					$link_name  = 'back_to_index_link';
					$link_title = 'back_to_index_link_title';
				}

				if (isset($link_title)) {
					if ($back == 'entry')
						$subnav_link = array(
							'id' => $id,
							'title' => $link_title,
							'name' => $link_name
						);
					else
						$subnav_link = array(
							'mode' => $back,
							'id' => $id,
							'title' => $link_title,
							'name' => $link_name
						);
				} else {
					$subnav_link = array(
						'mode' => 'index',
						'name' => 'forum_index_link',
						'title' => 'forum_index_link_title'
					);
				}
				$smarty->assign('subnav_link', $subnav_link);
			} elseif ($posting_mode == 1) {
				// edit message
				$edit_result = mysqli_query($connid, "SELECT " . $db_settings['forum_table'] . ".user_id, name, user_name, locked, UNIX_TIMESTAMP(time) AS time FROM " . $db_settings['forum_table'] . "
				LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = " . $db_settings['forum_table'] . ".user_id
				WHERE id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($edit_result) != 1)
					$errors[] = 'error_posting_unavailable';
				$field = mysqli_fetch_array($edit_result);
				mysqli_free_result($edit_result);

				if (empty($errors)) {
					if (empty($name) && $field['user_id'] > 0)
						$name = $field['user_name'];
					if ($field['user_id'] == 0)
						$subnav_name = $field['name'];
					elseif ($field['user_id'] > 0 && isset($name))
						$subnav_name = $name;
					else
						$subnav_name = $lang['unknown_user'];

					$subnav_link = array(
						'mode' => $back,
						'id' => $id,
						'title' => 'back_to_entry_link_title',
						'name' => 'back_to_entry_link'
					);
					$smarty->assign('subnav_link', $subnav_link);
					$smarty->assign('name_repl_subnav', htmlspecialchars($subnav_name));

					if ($settings['edit_max_time_period'] > 0 && (empty($_SESSION[$settings['session_prefix'] . 'user_type']) || isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] == 0)) {
						$minutes_left_to_edit = round((($field['time'] + $settings['edit_max_time_period'] * 60) - TIMESTAMP) / 60);
						if ($minutes_left_to_edit >= 0)
							$smarty->assign('minutes_left_to_edit', $minutes_left_to_edit);
					}
				}
			}

			// check data:
			/* Tags
				0 == Off
				1 == Admins/Mods
				2 == reg. Users
				3 == everyone
			*/
			if (isset($_POST['tags']) && isset($settings['tags']) && $settings['tags'] > 0 &&
					(($settings['tags'] > 2) ||
					($settings['tags'] == 2 && isset($_SESSION[$settings['session_prefix'] . 'user_type'])) ||
					($settings['tags'] == 1 && isset($_SESSION[$settings['session_prefix'] . 'user_type']) && ($_SESSION[$settings['session_prefix'] . 'user_type'] > 0))
				)) {
				$tagStr = trim($_POST['tags']);
				$tagsArray = array_filter(array_map('trim', explode(',', $tagStr)), function($value) { return $value !== ''; });


				foreach ($tagsArray as $tag) {
					unset($too_long_word);
					$too_long_word = too_long_word($tag, $settings['text_word_maxlength'], $lang['word_delimiters']);
					if ($too_long_word) { 
						$errors[] = 'error_word_too_long'; 
						break;
					}
				}
			} 
			else
				$tagsArray = false;


			if ($posting_mode == 1) {
				$edit_result = mysqli_query($connid, "SELECT tid, pid, name, user_id, email, hp, location, subject, text, category, show_signature, sticky, locked, UNIX_TIMESTAMP(time) AS time, edit_key FROM " . $db_settings['forum_table'] . " WHERE id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));
				$field = mysqli_fetch_array($edit_result);
				mysqli_free_result($edit_result);
				$pid = $field['pid'];
				$field['tags'] = getBookmarkTags($id);

				// authorisatin check:
				$authorization = get_edit_authorization($id, $field['user_id'], $field['edit_key'], $field['time'], $field['locked']);
				if ($authorization['edit'] != true) {
					$errors[] = 'no_authorization_edit';
				}
			}

			if ($posting_mode == 1 && empty($errors)) {
				// e-mail available for notification?
				if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
					if ($field['user_id'] == 0 && empty($email) && isset($email_notification) && $email_notification == 1)
						$errors[] = 'error_no_email_to_notify';
				}
			}

			if (empty($errors)) {
				// category check:
				if ($id == 0 && $categories != false && empty($categories[$p_category]))
					$errors[] = 'error_invalid_category';

				// name reserved?
				$result = mysqli_query($connid, "SELECT user_id, user_name FROM " . $db_settings['userdata_table'] . " WHERE lower(user_name) = '" . mysqli_real_escape_string($connid, my_strtolower($name, $lang['charset'])) . "'") or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) > 0) {
					if (empty($_SESSION[$settings['session_prefix'] . 'user_id'])) {
						$errors[] = 'error_name_reserved';
					} elseif (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
						$data = mysqli_fetch_array($result);
						if (isset($posting_user_id) && $data['user_id'] != $posting_user_id)
							$errors[] = 'error_name_reserved';
					}
				}
				mysqli_free_result($result);

				// check for not accepted words:
				$joined_message     = my_strtolower($name . ' ' . $email . ' ' . $hp . ' ' . $location . ' ' . $subject . ' ' . $text, $lang['charset']);
				$not_accepted_words = get_not_accepted_words($joined_message);
				if ($not_accepted_words != false) {
					$not_accepted_words_listing = implode(', ', $not_accepted_words);
					if (count($not_accepted_words) == 1) {
						$smarty->assign('not_accepted_word', htmlspecialchars($not_accepted_words_listing));
						$errors[] = 'error_not_accepted_word';
					} else {
						$smarty->assign('not_accepted_words', htmlspecialchars($not_accepted_words_listing));
						$errors[] = 'error_not_accepted_words';
					}
				}

				if (empty($_SESSION[$settings['session_prefix'] . 'user_id']) || ($posting_mode == 1 && $field['user_id'] == 0)) {
					if (empty($name))
						$errors[] = 'error_no_name';
					if (contains_special_characters($name))
						$errors[] = 'error_username_invalid_chars';
					if (!empty($name) && !empty($subject) && my_strtolower($name, $lang['charset']) == my_strtolower($subject, $lang['charset']))
						$errors[] = 'error_name_like_subject';
					if (my_strlen($name, $lang['charset']) > $settings['username_maxlength'])
						$errors[] = 'error_name_too_long';
					if (my_strlen($email, $lang['charset']) > $settings['email_maxlength'])
						$errors[] = 'error_email_too_long';
					if (isset($hp) && my_strlen($hp, $lang['charset']) > $settings['hp_maxlength'])
						$errors[] = 'error_hp_too_long';
					if (my_strlen($location, $lang['charset']) > $settings['location_maxlength'])
						$errors[] = 'error_location_too_long';
				}

				if (isset($email) && $email != '' && !is_valid_email($email))
					$errors[] = 'error_email_wrong';
				if (isset($hp) && $hp != '' && !is_valid_url($hp))
					$errors[] = 'error_hp_wrong';
				if (empty($email) && isset($email_notification) && $email_notification == 1 && !isset($_SESSION[$settings['session_prefix'] . 'user_id']))
					$errors[] = 'error_no_email_to_notify';
				if (empty($subject))
					$errors[] = 'error_no_subject';
				if (empty($text) && $settings['empty_postings_possible'] == 0)
					$errors[] = 'error_no_text';
				if (my_strlen($subject, $lang['charset']) > $settings['subject_maxlength'])
					$errors[] = 'error_subject_too_long';
				if (my_strlen($text, $lang['charset']) > $settings['text_maxlength'])
					$errors[] = 'error_text_too_long';
				$smarty->assign('text_length', my_strlen($text, $lang['charset']));

				// check for too long words:
				if (empty($too_long_word) && empty($_SESSION[$settings['session_prefix'] . 'user_id'])) {
					$too_long_word = too_long_word($name, $settings['name_word_maxlength'], $lang['word_delimiters']);
					if ($too_long_word)
						$errors[] = 'error_word_too_long';
				}
				if (empty($too_long_word) && empty($_SESSION[$settings['session_prefix'] . 'user_id'])) {
					$too_long_word = too_long_word($location, $settings['location_word_maxlength'], $lang['word_delimiters']);
					if ($too_long_word)
						$errors[] = 'error_word_too_long';
				}
				if (empty($too_long_word)) {
					$too_long_word = too_long_word($subject, $settings['subject_word_maxlength'], $lang['word_delimiters']);
					if ($too_long_word)
						$errors[] = 'error_word_too_long';
				}

				// format text and hide allowed tags:
				$check_text = html_format($text);
				$check_text = preg_replace("#\<pre(.+?)\>(.+?)\</pre\>#is", "", $check_text);
				$check_text = strip_tags($check_text);

				if (empty($too_long_word)) {
					$too_long_word = too_long_word($check_text, $settings['text_word_maxlength'], $lang['word_delimiters']);
					if ($too_long_word)
						$errors[] = 'error_word_too_long';
				}
				if (empty($_SESSION[$settings['session_prefix'] . 'user_id'])) {
					if ($settings['terms_of_use_agreement'] == 1 && $terms_of_use_agree != 1)
						$errors[] = 'terms_of_use_error_posting';
					if ($settings['data_privacy_agreement'] == 1 && $data_privacy_agree != 1)
						$errors[] = 'data_priv_statement_error_post';
				}
			}

			// CAPTCHA check:
			if (empty($errors) && isset($_POST['save_entry']) && empty($_SESSION[$settings['session_prefix'] . 'user_id']) && $settings['captcha_posting'] > 0) {
				if ($settings['captcha_posting'] == 2) {
					if (empty($_SESSION['captcha_session']) || empty($_POST['captcha_code']) || $captcha->check_captcha($_SESSION['captcha_session'], $_POST['captcha_code']) != true)
						$errors[] = 'captcha_check_failed';
				} else {
					if (empty($_SESSION['captcha_session']) || empty($_POST['captcha_code']) || $captcha->check_math_captcha($_SESSION['captcha_session'][2], $_POST['captcha_code']) != true)
						$errors[] = 'captcha_check_failed';
				}
				unset($_SESSION['captcha_session']);
			}

			// default Akismet values for flagging spam:
			$akismet_spam              = 0;
			$akismet_spam_check_status = 0;

			// default B8 values for flagging spam:
			$b8_spam             = 0; // 0 == HAM, 1 == SPAM
			$b8_spam_rating      = 0; // 0 == no decision, 1 == learn(ed) ham, 2 == learn(ed) spam

			// Akismet and B8 spam check:
			if (empty($errors) && isset($_POST['save_entry'])) {
				if (empty($_SESSION[$settings['session_prefix'] . 'user_id']) || isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $settings['spam_check_registered'] == 1) {
					$is_mod_or_admin = isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0;
					// fetch user data if registered user:
					$check_posting = [];
					if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
						$akismet_userdata_result = mysqli_query($connid, "SELECT user_email, user_hp FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($_SESSION[$settings['session_prefix'] . 'user_id']) . " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
						$akismet_userdata_data = mysqli_fetch_array($akismet_userdata_result);
						mysqli_free_result($akismet_userdata_result);
						if ($akismet_userdata_data['user_hp'] != '')
							$check_posting['website'] = $akismet_userdata_data['user_hp'];
					} else {
						if ($email != '')
							$check_posting['email'] = $email;
						if ($hp != '')
							$check_posting['website'] = $hp;
					}
					$check_posting['author'] = $name;
					$check_posting['body']   = $text;

					// check only postings of users
					if ($settings['akismet_key'] != '' && $settings['akismet_entry_check'] == 1 && !$is_mod_or_admin) {
						require('modules/akismet/akismet.class.php');
						$akismet = new Akismet($settings['forum_address'], $settings['akismet_key'], $check_posting);
						// test for errors
						if ($akismet->errorsExist()) {
							// returns true if any errors exist
							if ($akismet->isError(AKISMET_INVALID_KEY)) {
								if ($settings['save_spam'] == 0)
									$errors[] = 'error_akismet_api_key';
								else
									$akismet_spam_check_status = 3;
							} elseif ($akismet->isError(AKISMET_RESPONSE_FAILED)) {
								if ($settings['save_spam'] == 0)
									$errors[] = 'error_akismet_connection';
								else
									$akismet_spam_check_status = 2;
							} elseif ($akismet->isError(AKISMET_SERVER_NOT_FOUND)) {
								if ($settings['save_spam'] == 0)
									$errors[] = 'error_akismet_connection';
								else
									$akismet_spam_check_status = 2;
							}
						} else {
							// No errors, check for spam
							if ($akismet->isSpam()) {
								if ($settings['save_spam'] == 0)
									$errors[] = 'error_spam_suspicion';
								else
									$akismet_spam = 1;
							} else {
								$akismet_spam_check_status = 1;
							}
						}
					}

					if ($settings['b8_entry_check'] == 1) {
						try {
							// unlearn edited posting to avoid redundantly training data 
							if ($posting_mode == 1) { // edited posting
								$b8_spam_or_ham_result = mysqli_query($connid, "SELECT `training_type` FROM `" . $db_settings['b8_rating_table'] . "` WHERE `eid` = " . intval($id) . " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
								$b8_spam_or_ham_data   = mysqli_fetch_array($b8_spam_or_ham_result);
								mysqli_free_result($b8_spam_or_ham_result);

								if (isset($b8_spam_or_ham_data['training_type']) && $b8_spam_or_ham_data['training_type'] != 0) { // 0 == no decision, 1 == learn(ed) ham, 2 == learn(ed) spam
									$original_unedited_posting = [];
									$original_unedited_posting['author']  = empty($field['name']) ? $name : $field['name'];
									$original_unedited_posting['email']   = $field['email'];
									$original_unedited_posting['website'] = $field['hp'];
									$original_unedited_posting['body']    = $field['text'];
									$original_unedited_text = implode("\r\n", $original_unedited_posting);

									if ($b8_spam_or_ham_data['training_type'] == 1)  // original (unedited) entry was flaged as HAM
										$B8_BAYES_FILTER->unlearn($original_unedited_text, b8\b8::HAM);
									elseif ($b8_spam_or_ham_data['training_type'] == 2) // original (unedited) entry was flaged as SPAM
										$B8_BAYES_FILTER->unlearn($original_unedited_text, b8\b8::SPAM);
								}
							}

							$check_text = implode("\r\n", $check_posting);
							$b8_spam_probability = 100.0 * $B8_BAYES_FILTER->classify($check_text);
							// postings of admins/mods are always HAM, postings of users are checked
							$b8_spam = $b8_spam_probability >           intval($settings['b8_spam_probability_threshold'])  && !$is_mod_or_admin;
							$b8_ham  = $b8_spam_probability <= (100.0 - intval($settings['b8_spam_probability_threshold'])) ||  $is_mod_or_admin;

							if ($settings['b8_auto_training'] == 1) {
								if ($b8_spam) {
									$b8_spam_rating = 2;  // SPAM
									$B8_BAYES_FILTER->learn($check_text, b8\b8::SPAM);
								}
								elseif ($b8_ham) {
									$b8_spam_rating = 1;  // HAM
									$B8_BAYES_FILTER->learn($check_text, b8\b8::HAM);
								}
								else
									$b8_spam_rating = 0;  // No Decision
							}

							// check, if SPAM should stored to DB
							if ($b8_spam && $settings['save_spam'] == 0)
								$errors[] = 'error_spam_suspicion';
						}
						catch(Exception $e) {
							raise_error('database_error', $e->getMessage()); // What should we do here?
							$b8_spam = 0;
						}
					}
				}
			} // end check data

			if (empty($errors)) {
				// save new posting:
				if (isset($_POST['save_entry']) && $posting_mode == 0) {
					if ($settings['entries_by_users_only'] != 0 && empty($_SESSION[$settings['session_prefix'] . 'user_name']))
						die('No autorisation!');

					// if editing own postings by unregistered users, generate edit_key:
					if (empty($_SESSION[$settings['session_prefix'] . 'user_id']) && $settings['user_edit'] == 2) {
						$edit_key      = random_string(32);
						$edit_key_hash = generate_pw_hash($edit_key);
					} else {
						$edit_key      = '';
						$edit_key_hash = '';
					}
					// Akismet *OR* B8 flagged entry as SPAM
					$spam = $akismet_spam == 1 || $b8_spam == 1 ? 1 : 0;
					$locked = $spam == 0 ? 0 : 1;

					if (isset($_SESSION[$settings['session_prefix'] . 'user_id']))
						$savename = '';
					else
						$savename = $name;

					@mysqli_query($connid, "INSERT INTO " . $db_settings['forum_table'] . " (pid, tid, uniqid, time, last_reply, user_id, name, subject, email, hp, location, ip, text, show_signature, category, locked, sticky, edit_key) VALUES (" . intval($id) . ", " . intval($thread) . ", '" . mysqli_real_escape_string($connid, $uniqid) . "', NOW(), NOW()," . intval($user_id) . ", '" . mysqli_real_escape_string($connid, $savename) . "', '" . mysqli_real_escape_string($connid, $subject) . "', '" . mysqli_real_escape_string($connid, $email) . "', '" . mysqli_real_escape_string($connid, $hp) . "', '" . mysqli_real_escape_string($connid, $location) . "', '" . mysqli_real_escape_string($connid, $_SERVER["REMOTE_ADDR"]) . "', '" . mysqli_real_escape_string($connid, $text) . "', " . intval($show_signature) . ", " . intval($p_category) . ", " . intval($locked) . ", " . intval($sticky) . ", '" . mysqli_real_escape_string($connid, $edit_key_hash) . "')") or raise_error('database_error', mysqli_error($connid));
					$newID = mysqli_insert_id($connid);

					if ($id == 0) {
						// new thread, set thread id:
						@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET tid = id, time = time WHERE id = " . intval($newID)) or raise_error('database_error', mysqli_error($connid));
					}
					// reply, set last reply:
					if ($id != 0 && $spam == 0) {
						@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time = time, last_reply = NOW() WHERE tid = " . $thread) or raise_error('database_error', mysqli_error($connid));
					}

					if (intval($newID) > 0) {
						// save spam rating
						@mysqli_query($connid, "INSERT INTO " . $db_settings['akismet_rating_table'] . " (`eid`, `spam`, `spam_check_status`) VALUES (" . intval($newID) . ", " . intval($akismet_spam) . ", " . intval($akismet_spam_check_status) . ")") or raise_error('database_error', mysqli_error($connid));
						@mysqli_query($connid, "INSERT INTO " . $db_settings['b8_rating_table'] . " (`eid`, `spam`, `training_type`) VALUES (" . intval($newID) . ", " . intval($b8_spam) . ", " . intval($b8_spam_rating) . ")") or raise_error('database_error', mysqli_error($connid));
					}

					if (intval($email_notification) === 1 and intval($newID) > 0) {
						@mysqli_query($connid, "INSERT INTO " .$db_settings['subscriptions_table']. " (user_id, eid, unsubscribe_code, tstamp) VALUES (" . ($user_id > 0 ? intval($user_id) : "NULL") . ", " . intval($newID) . ", UUID(), NOW());") or raise_error('database_error', mysqli_error($connid));
					}

					// get last entry:
					$result_new = mysqli_query($connid, "SELECT tid, pid, id FROM " . $db_settings['forum_table'] . " WHERE id = " . intval($newID));
					$new_data   = mysqli_fetch_array($result_new);
					mysqli_free_result($result_new);

					// save new of posting tags:
					if (isset($tagsArray) && $tagsArray)
						setEntryTags($new_data['id'], $tagsArray);

					// if editing own postings by unregistered users, set set cookie/session with edit_key:
					if (empty($_SESSION[$settings['session_prefix'] . 'user_id']) && $settings['user_edit'] == 2 && isset($edit_key) && $edit_key != '') {
						$_SESSION[$settings['session_prefix'] . 'edit_keys'][$new_data['id']] = $edit_key;
					}

					// lock thread if auto_lock is enabled and there are more than the maximum number of entries in the thread:
					if ($settings['auto_lock'] > 0 && $new_data['pid'] != 0) {
						list($thread_count) = mysqli_fetch_row(mysqli_query($connid, "SELECT COUNT(*) FROM " . $db_settings['forum_table'] . " WHERE tid = " . intval($new_data['tid'])));
						if ($thread_count >= $settings['auto_lock'])
							@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time = time, last_reply = last_reply, edited = edited, locked = 1 WHERE tid = " . intval($new_data['tid']));
					}

					// load e-mail strings from language file:
					$smarty->configLoad($settings['language_file'], 'emails');
					$lang = $smarty->getConfigVars();
					if ($language_file != $settings['language_file'])
						setlocale(LC_ALL, $lang['locale']);

					// e-mail notification:
					if ($spam == 0)
						emailNotification2ParentAuthor($new_data['id']);

					// e-mail notification to admins and mods
					if ($spam == 0)
						emailNotification2ModsAndAdmins($new_data['id']);

					// set userdata cookie:
					if ($settings['remember_userdata'] && isset($setcookie) && $setcookie == 1) {
						$cookie_data = urlencode($name) . '|' . urlencode($email) . '|' . urlencode($hp) . '|' . urlencode($location);
						setcookie($settings['session_prefix'] . 'userdata', $cookie_data, TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days']));
					}
					// If the posting is classified as SPAM, redirect the user to the single entry view, which contains the SPAM warning message
					if (isset($back) && $back == 'thread' && $spam == 0)
						header('Location: index.php?mode=thread&id=' . $new_data['id'] . '#p' . $new_data['id']);
					else
						header('Location: index.php?id=' . $new_data['id']);
					exit;
				} elseif (isset($_POST['save_entry']) && $posting_mode == 1) {
					// save edited posting:
					$tid_result = @mysqli_query($connid, "SELECT pid, tid, name, location, user_id, category, subject, text FROM " . $db_settings['forum_table'] . " WHERE id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));
					$field = mysqli_fetch_array($tid_result);
					mysqli_free_result($tid_result);

					// set category if not initial posting:
					if ($field['pid'] != 0) {
						$p_category = intval($field['category']);
					}

					// Akismet *OR* B8 flagged entry as SPAM
					$spam = $akismet_spam == 1 || $b8_spam == 1 ? 1 : 0;

					if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] == 2 && $settings['dont_reg_edit_by_admin'] == 1 || isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] == 1 && $settings['dont_reg_edit_by_mod'] == 1 || ($field['text'] == $text && $field['subject'] == $subject && ($field['user_id'] > 0 || $field['name'] == $name && $field['location'] == $location) && isset($_SESSION[$settings['session_prefix'] . 'user_type']) && ($_SESSION[$settings['session_prefix'] . 'user_type'] == 2 && $settings['dont_reg_edit_by_admin'] == 1 || $_SESSION[$settings['session_prefix'] . 'user_type'] == 1 && $settings['dont_reg_edit_by_mod'] == 1))) {
						// unnoticed editing for admins and mods
						$edited_query    = 'edited';
						$edited_by_query = 'edited_by';
						$locked_query    = 'locked';
					} elseif (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
						$edited_query    = 'NOW()';
						$edited_by_query = intval($_SESSION[$settings['session_prefix'] . 'user_id']);
						$locked_query    = 'locked';
					} else {
						$edited_query    = 'NOW()';
						$edited_by_query = 0;
						$locked_query    = $spam == 0 ? 'locked' : 1;
					}

					if ($field['user_id'] > 0) {
						// posting of a registered user edited
						@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time = time, last_reply = last_reply, edited = " . $edited_query . ", edited_by = " . $edited_by_query . ", subject = '" . mysqli_real_escape_string($connid, $subject) . "', category = " . intval($p_category) . ", email = '" . mysqli_real_escape_string($connid, $email) . "', hp = '" . mysqli_real_escape_string($connid, $hp) . "', location = '" . mysqli_real_escape_string($connid, $location) . "', text = '" . mysqli_real_escape_string($connid, $text) . "', show_signature = " . intval($show_signature) . " " . ((isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) ? ", sticky = " . intval($sticky) : "") . " WHERE id = " . intval($id));
						// save new of posting tags:
						if (isset($tagsArray) && $tagsArray) {
							setEntryTags($id, $tagsArray);
						}
						// save status of email notification
						if ($email_notification == 1) {
							@mysqli_query($connid, "INSERT INTO " . $db_settings['subscriptions_table'] . " (user_id, eid, unsubscribe_code, tstamp) VALUES (" . intval($field['user_id']) . ", " . intval($id) . ", UUID(), NOW()) ON DUPLICATE KEY UPDATE eid = eid, user_id = user_id") or die(mysqli_error($connid));
						} else {
							@mysqli_query($connid, "DELETE FROM " . $db_settings['subscriptions_table'] . " WHERE eid = " . intval($id) . " AND user_id = " . intval($field['user_id'])) or die(mysqli_error($connid));
						}
					} else {
						// posting of a not registed user edited
						@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time = time, last_reply = last_reply, edited = " . $edited_query . ", edited_by = " . intval($edited_by_query) . ", name = '" . mysqli_real_escape_string($connid, $name) . "', subject = '" . mysqli_real_escape_string($connid, $subject) . "', category = " . intval($p_category) . ", email = '" . mysqli_real_escape_string($connid, $email) . "', hp = '" . mysqli_real_escape_string($connid, $hp) . "', location = '" . mysqli_real_escape_string($connid, $location) . "', text = '" . mysqli_real_escape_string($connid, $text) . "', show_signature = " . intval($show_signature) . ", locked = " . $locked_query . ", sticky = " . intval($sticky) . " WHERE id = " . intval($id)) or die(mysqli_error($connid));
						if (isset($tagsArray) && $tagsArray) {
							setEntryTags($id, $tagsArray);
						}
						// save status of email notification
						if ($email_notification == 1) {
							@mysqli_query($connid, "INSERT INTO " . $db_settings['subscriptions_table'] . " (user_id, eid, unsubscribe_code, tstamp) VALUES (NULL, " . intval($id) . ", UUID(), NOW()) ON DUPLICATE KEY UPDATE eid = eid, user_id = user_id") or die(mysqli_error($connid));
						} else {
							@mysqli_query($connid, "DELETE FROM " . $db_settings['subscriptions_table'] . " WHERE eid = " . intval($id) . " AND user_id IS NULL") or die(mysqli_error($connid));
						}
					}

					// save spam rating
					@mysqli_query($connid, "REPLACE INTO " . $db_settings['akismet_rating_table'] . " (`eid`, `spam`, `spam_check_status`) VALUES (" . intval($id) . ", " . intval($b8_spam) . ", " . intval($b8_spam_rating) . ")");
					@mysqli_query($connid, "REPLACE INTO " . $db_settings['b8_rating_table'] . " (`eid`, `spam`, `training_type`) VALUES (" . intval($id) . ", " . intval($b8_spam) . ", " . intval($b8_spam_rating) . ")");

					$category_update_result = mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time = time, last_reply = last_reply, edited = edited, category = " . intval($p_category) . " WHERE tid = " . intval($field['tid']));
					@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_cache_table'] . " WHERE cache_id = " . intval($id));
					header('location: index.php?mode=' . $back . '&id=' . $id);
					exit;
				}
			}

			// preview:
			if (isset($_POST['preview']) && empty($errors)) {
				$smarty->assign('preview', true);
				if (isset($posting_user_id) && intval($posting_user_id) > 0) {
					$pr_result = @mysqli_query($connid, "SELECT email_contact, user_hp, user_location, signature FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($posting_user_id) . " LIMIT 1") or die(mysqli_error($connid));
					$pr_data = mysqli_fetch_array($pr_result);
					mysqli_free_result($pr_result);
					if ($isModOrAdmin || $isUser && $pr_data['email_contact'] > 0 || $pr_data['email_contact'] == 2)
						$smarty->assign('email', true);
					if (!empty($pr_data['user_hp']) && trim($pr_data['user_hp']) != '') {
						$smarty->assign('preview_hp', htmlspecialchars(add_http_if_no_protocol($pr_data['user_hp'])));
					}
					if (!empty($pr_data['user_location']) && trim($pr_data['user_location']) != '')
						$smarty->assign('preview_location', htmlspecialchars($pr_data['user_location']));
					if (!empty($pr_data['signature']) && trim($pr_data['signature']) != '')
						$smarty->assign('preview_signature', signature_format($pr_data['signature']));
					if ($pr_data['signature'] != '') {
						$smarty->assign('signature', true);
						$smarty->assign('show_signature', $show_signature);
					}
					$smarty->assign('provide_email_notification', true);
				} else {
					$smarty->assign('email', htmlspecialchars($email));
					if (!empty($hp) && trim($hp) != '') {
						$smarty->assign('preview_hp', htmlspecialchars(add_http_if_no_protocol($hp)));
					}
					$smarty->assign('hp', htmlspecialchars($hp));
					$smarty->assign('location', htmlspecialchars($location));
					$smarty->assign('preview_location', htmlspecialchars($location));
					if ($settings['email_notification_unregistered'])
						$smarty->assign('provide_email_notification', true);
				}
				if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0 && (empty($id) || $posting_mode == 1 && $pid == 0))
					$smarty->assign('provide_sticky', true);
				// current time:
				list($preview_time) = mysqli_fetch_row(mysqli_query($connid, "SELECT UNIX_TIMESTAMP(NOW() + INTERVAL " . $time_difference . " MINUTE)"));
				$smarty->assign('preview_timestamp', $preview_time);
				$preview_formated_time = format_time($lang['time_format_full'], $preview_time);
				$smarty->assign('preview_formated_time', $preview_formated_time);
				$smarty->assign('uniqid', htmlspecialchars($uniqid));
				$smarty->assign('posting_mode', intval($posting_mode));
				$smarty->assign('fld_user_name', $fname_user);
				$smarty->assign('fld_user_email', $fname_email);
				$smarty->assign('fld_phone', $fname_phone);
				$smarty->assign('fld_repeat_email', $fname_repemail);
				$smarty->assign('fld_hp', $fname_hp);
				$smarty->assign('fld_location', $fname_loc);
				$smarty->assign('fld_subject', $fname_subject);
				$smarty->assign('id', intval($id));
				$smarty->assign('pid', intval($pid));
				$smarty->assign('back', htmlspecialchars($back));
				$smarty->assign('name', htmlspecialchars($name));
				$smarty->assign('subject', htmlspecialchars($subject));
				$smarty->assign('text', htmlspecialchars($text));
				if (isset($_SESSION[$settings['session_prefix'] . 'user_type']))
					$smarty->assign('user_type', htmlspecialchars($_SESSION[$settings['session_prefix'] . 'user_type']));
				if (isset($_POST['repeat_email']))
					$smarty->assign('honey_pot_email',  htmlspecialchars($_POST['repeat_email']));
				if (isset($_POST['phone']))
					$smarty->assign('honey_pot_phone', htmlspecialchars($_POST['phone']));
				if (isset($_POST['tags']))
					$smarty->assign('tags', htmlspecialchars(trim($_POST['tags'])));
				if (isset($p_category))
					$smarty->assign('p_category', intval($p_category));
				if (isset($setcookie))
					$smarty->assign('setcookie', intval($setcookie));
				$smarty->assign('email_notification', intval($email_notification));
				$smarty->assign('sticky', intval($sticky));
				$smarty->assign('preview_name', htmlspecialchars($name));
				$preview_text = html_format($text);
				if ($settings['terms_of_use_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
					$smarty->assign("terms_of_use_agreement", true);
				if (isset($terms_of_use_agree))
					$smarty->assign('terms_of_use_agree', intval($terms_of_use_agree));
				if ($settings['data_privacy_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
					$smarty->assign("data_privacy_agreement", true);
				if (isset($data_privacy_agree))
					$smarty->assign('data_privacy_statement_agree', intval($data_privacy_agree));
				$smarty->assign('preview_text', $preview_text);
				$smarty->assign('preview_subject', htmlspecialchars($subject));
				$smarty->assign('subtemplate', 'posting.inc.tpl');
			}

			if (isset($errors)) {
				if (isset($show_signature))
					$smarty->assign('show_signature', $show_signature);
				if (isset($posting_user_id) && intval($posting_user_id) > 0) {
					list($signature) = @mysqli_fetch_row(mysqli_query($connid, "SELECT signature FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($posting_user_id)));
					if (!empty($signature)) {
						$smarty->assign('signature', true);
						$smarty->assign('show_signature', $show_signature);
					}
				}
				$smarty->assign('id', intval($id));
				$smarty->assign('pid', intval($pid));
				$smarty->assign('errors', $errors);
				if (isset($too_long_word))
					$smarty->assign('word', $too_long_word);
				$smarty->assign('uniqid', htmlspecialchars($uniqid));
				$smarty->assign('back', htmlspecialchars($back));
				$smarty->assign('posting_mode', intval($posting_mode));
				$smarty->assign('fld_user_name', $fname_user);
				$smarty->assign('fld_user_email', $fname_email);
				$smarty->assign('fld_phone', $fname_phone);
				$smarty->assign('fld_repeat_email', $fname_repemail);
				$smarty->assign('fld_hp', $fname_hp);
				$smarty->assign('fld_location', $fname_loc);
				$smarty->assign('fld_subject', $fname_subject);
				if (isset($name))
					$smarty->assign('name', htmlspecialchars($name));
				$smarty->assign('email', htmlspecialchars($email));
				$smarty->assign('hp', htmlspecialchars($hp));
				$smarty->assign('location', htmlspecialchars($location));
				$smarty->assign('subject', htmlspecialchars($subject));
				$smarty->assign('text', htmlspecialchars($text));
				if (isset($_POST['tags']))
					$smarty->assign('tags', htmlspecialchars(trim($_POST['tags'])));
				if (isset($p_category))
					$smarty->assign('p_category', intval($p_category));
				if (isset($setcookie))
					$smarty->assign('setcookie', intval($setcookie));
				$smarty->assign('email_notification', intval($email_notification));
				$smarty->assign('sticky', intval($sticky));
				if ((isset($posting_user_id) && intval($posting_user_id) > 0) || $settings['email_notification_unregistered'] == 1)
					$smarty->assign('provide_email_notification', true);
				if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0 && (empty($id) || $posting_mode == 1 && $pid == 0))
					$smarty->assign('provide_sticky', true);
				if ($settings['terms_of_use_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
					$smarty->assign("terms_of_use_agreement", true);
				if (isset($terms_of_use_agree))
					$smarty->assign('terms_of_use_agree', intval($terms_of_use_agree));
				if ($settings['data_privacy_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
					$smarty->assign("data_privacy_agreement", true);
				if (isset($data_privacy_agree))
					$smarty->assign('data_privacy_statement_agree', intval($data_privacy_agree));
				$smarty->assign('subtemplate', 'posting.inc.tpl');
			}
		}
		break;
	case 'edit':
		if ($settings['forum_readonly'] == 1) {
			$subnav_link = array(
				'mode' => 'index',
				'name' => 'forum_index_link',
				'title' => 'forum_index_link_title'
			);
			$smarty->assign("subnav_link", $subnav_link);
			$smarty->assign('no_authorisation', 'no_auth_readonly');
			$smarty->assign('subtemplate', 'posting.inc.tpl');
			break;
		}

		$id = intval($_GET['edit']);
		$edit_result = @mysqli_query($connid, "SELECT tid, pid, name, user_name, " . $db_settings['forum_table'] . ".user_id, email, hp, location, subject, UNIX_TIMESTAMP(time) AS time, text, category, show_signature, sticky, locked, edit_key
		FROM " . $db_settings['forum_table'] . "
		LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = " . $db_settings['forum_table'] . ".user_id
		WHERE id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));
		if (mysqli_num_rows($edit_result) != 1) {
			$smarty->assign('no_authorisation', 'error_posting_unavailable');
			$subnav_link = array(
				'mode' => 'index',
				'name' => 'thread_entry_back_link',
				'title' => 'thread_entry_back_title'
			);
			$smarty->assign("subnav_link", $subnav_link);
			$smarty->assign('subtemplate', 'posting.inc.tpl');
			break;
		}

		$field = mysqli_fetch_array($edit_result);
		$field['tags'] = getEntryTags($id);
		mysqli_free_result($edit_result);
		// check for notification
		if ($field['user_id'] > 0) {
			$subscription = @mysqli_query($connid, "SELECT eid FROM " . $db_settings['subscriptions_table'] . " WHERE eid = " . intval($id) . " AND user_id = " . intval($field['user_id'])) or raise_error('database_error', mysqli_error($connid));
		} else {
			$subscription = @mysqli_query($connid, "SELECT eid FROM " . $db_settings['subscriptions_table'] . " WHERE eid = " . intval($id) . " AND user_id IS NULL") or raise_error('database_error', mysqli_error($connid));
		}
		if (mysqli_num_rows($subscription) == 1) {
			$field['email_notification'] = 1;
		} else {
			$field['email_notification'] = 0;
		}

		// authorisatin check:
		$authorization = get_edit_authorization($id, $field['user_id'], $field['edit_key'], $field['time'], $field['locked']);
		if ($authorization['edit'] != true) {
			$smarty->assign('no_authorisation', 'no_authorization_edit');
		} elseif ($authorization['edit'] == true) {
			if (!empty($field['tags']))
				$tags = implode(", ", array_filter(array_map('htmlspecialchars', $field['tags']), function($value) { return $value !== ''; }));
			else
				$tags = '';

			// if posting is by a registered user, check if he has signature
			if ($field['user_id'] > 0) {
				list($signature) = @mysqli_fetch_row(mysqli_query($connid, "SELECT signature FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($field['user_id'])));
				if (!empty($signature)) {
					$smarty->assign('signature', true);
					$smarty->assign('show_signature', $field['show_signature']);
				}
			}

			if ($settings['edit_max_time_period'] > 0 && (empty($_SESSION[$settings['session_prefix'] . 'user_type']) || isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] == 0)) {
				$minutes_left_to_edit = round((($field['time'] + $settings['edit_max_time_period'] * 60) - TIMESTAMP) / 60);
				$smarty->assign('minutes_left_to_edit', $minutes_left_to_edit);
			}

			$smarty->assign('id', intval($id));
			$smarty->assign('pid', intval($field['pid']));
			$smarty->assign('fld_user_name', $fname_user);
			$smarty->assign('fld_user_email', $fname_email);
			$smarty->assign('fld_phone', $fname_phone);
			$smarty->assign('fld_repeat_email', $fname_repemail);
			$smarty->assign('fld_hp', $fname_hp);
			$smarty->assign('fld_location', $fname_loc);
			$smarty->assign('fld_subject', $fname_subject);
			$smarty->assign('name', htmlspecialchars($field['name']));
			$smarty->assign('email', htmlspecialchars($field['email']));
			$smarty->assign('hp', htmlspecialchars($field['hp']));
			$smarty->assign('location', htmlspecialchars($field['location']));
			$smarty->assign('subject', htmlspecialchars($field['subject']));
			$smarty->assign('text', htmlspecialchars($field['text']));
			$smarty->assign('tags', $tags);
			$smarty->assign('p_category', $field['category']);
			$smarty->assign('email_notification', $field['email_notification']);
			$smarty->assign('sticky', $field['sticky']);
			$smarty->assign('back', $back);
			$smarty->assign('posting_mode', 1);
			if ($settings['terms_of_use_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
				$smarty->assign("terms_of_use_agreement", true);
			if ($settings['data_privacy_agreement'] == 1 && empty($_SESSION[$settings['session_prefix'] . 'user_id']))
				$smarty->assign("data_privacy_agreement", true);
		}

		if ($field['user_id'] > 0) {
			if (!$field['user_name'])
				$smarty->assign('name_repl_subnav', $lang['unknown_user']);
			else
				$smarty->assign('name_repl_subnav', htmlspecialchars($field['user_name']));
		} else
			$smarty->assign('name_repl_subnav', htmlspecialchars($field['name']));

		if ($field['user_id'] > 0 || $settings['email_notification_unregistered'])
			$smarty->assign('provide_email_notification', true);
		if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0 && (empty($id) || $posting_mode == 1 && $field['pid'] == 0))
			$smarty->assign('provide_sticky', true);

		$subnav_link = array(
			'mode' => $back,
			'id' => $id,
			'title' => 'back_to_entry_link_title',
			'name' => 'back_to_entry_link'
		);
		$smarty->assign('subnav_link', $subnav_link);
		$smarty->assign('subtemplate', 'posting.inc.tpl');
		break;
	case 'delete_posting':
		$delete_check_result = @mysqli_query($connid, "SELECT pid, name, user_name, subject, " . $db_settings['forum_table'] . ".user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL " . $time_difference . " MINUTE) AS disp_time, locked, edit_key
		FROM " . $db_settings['forum_table'] . "
		LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = " . $db_settings['forum_table'] . ".user_id
		WHERE id = " . intval($_REQUEST['delete_posting'])) or raise_error('database_error', mysqli_error($connid));
		$field = mysqli_fetch_array($delete_check_result);
		mysqli_free_result($delete_check_result);

		if ($field['user_id'] > 0) {
			if (!$field['user_name'])
				$field['name'] = $lang['unknown_user'];
			else
				$field['name'] = $field['user_name'];
		}

		$authorization = get_edit_authorization(intval($_REQUEST['delete_posting']), $field['user_id'], $field['edit_key'], $field['time'], $field['locked']);

		if ($authorization['delete'] == true) {
			$smarty->assign('id', intval($_REQUEST['delete_posting']));
			$smarty->assign('pid', intval($field['pid']));
			$smarty->assign('name', htmlspecialchars($field['name']));
			$smarty->assign('subject', htmlspecialchars($field['subject']));
			$smarty->assign('formated_time', format_time($lang['time_format_full'], $field['disp_time']));
			if (isset($_REQUEST['back']) && ($_REQUEST['back'] == 'entry' || $_REQUEST['back'] == 'thread' || $_REQUEST['back'] == 'index'))
				$smarty->assign('back', htmlspecialchars($_REQUEST['back']));
			$smarty->assign('category', $category);
			$smarty->assign('posting_mode', 1);
		} else {
			$smarty->assign('no_authorisation', 'no_authorisation_delete');
		}
		$smarty->assign('name_repl_subnav', htmlspecialchars($field['name']));
		$backlink    = 'entry';
		$subnav_link = array(
			'mode' => $back,
			'id' => intval($_REQUEST['delete_posting']),
			'title' => 'back_to_entry_link_title',
			'name' => 'back_to_entry_link'
		);
		$smarty->assign("subnav_link", $subnav_link);
		$smarty->assign('subtemplate', 'posting_delete.inc.tpl');
		break;
	case 'delete_posting_confirmed':
		$delete_check_result = mysqli_query($connid, "SELECT pid, name, subject, user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL " . $time_difference . " MINUTE) AS disp_time, locked, edit_key FROM " . $db_settings['forum_table'] . " WHERE id = " . intval($_REQUEST['delete_posting'])) or raise_error('database_error', mysqli_error($connid));
		$field = mysqli_fetch_array($delete_check_result);
		mysqli_free_result($delete_check_result);
		$authorization = get_edit_authorization(intval($_REQUEST['delete_posting']), $field['user_id'], $field['edit_key'], $field['time'], $field['locked']);
		if ($authorization['delete'] == true && isset($_REQUEST['csrf_token']) && $_REQUEST['csrf_token'] === $_SESSION['csrf_token']) {
			if (isset($_REQUEST['back'])) {
				$result = @mysqli_query($connid, "SELECT pid FROM " . $db_settings['forum_table'] . " WHERE id=" . intval($_REQUEST['delete_posting']) . " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) == 1) {
					$data = mysqli_fetch_array($result);
					if ($data['pid'] != 0)
						$pid = $data['pid'];
				}
			}
			delete_posting_recursive($_REQUEST['delete_posting']);
			if (isset($_REQUEST['back']) && $_REQUEST['back'] == 'entry' && isset($pid))
				header('Location: index.php?id=' . $pid);
			elseif (isset($_REQUEST['back']) && $_REQUEST['back'] == 'thread' && isset($pid))
				header('Location: index.php?mode=thread&id=' . $pid);
			else
				header('Location: index.php?mode=index');
			exit;
		} else {
			$smarty->assign('no_authorisation', 'no_authorisation_delete');
		}
		if ($field['name'])
			$smarty->assign('name_repl_subnav', htmlspecialchars($field['name']));
		else
			$smarty->assign('name_repl_subnav', $lang['unknown_user']);
		$backlink    = 'entry';
		$subnav_link = array(
			'mode' => $back,
			'id' => $id,
			'title' => 'back_to_entry_link_title',
			'name' => 'back_to_entry_link'
		);
		$smarty->assign("subnav_link", $subnav_link);
		$smarty->assign('subtemplate', 'posting_delete.inc.tpl');
		break;
	
		
	case 'move_posting':
		if (isset($_REQUEST['move_posting']) && intval($_REQUEST['move_posting']) > 0) {
			$move_result = @mysqli_query($connid, "SELECT pid, name, user_name, subject, " . $db_settings['forum_table'] . ".user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL " . $time_difference . " MINUTE) AS disp_time, locked, edit_key
			FROM " . $db_settings['forum_table'] . "
			LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = " . $db_settings['forum_table'] . ".user_id
			WHERE id = " . intval($_REQUEST['move_posting'])) or raise_error('database_error', mysqli_error($connid));
			$field = mysqli_fetch_array($move_result);
			if (mysqli_num_rows($move_result) == 1) {
				if ($field['user_id'] > 0) {
					if (!$field['user_name'])
						$field['name'] = $lang['unknown_user'];
					else
						$field['name'] = $field['user_name'];
				}
				if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) {
					$smarty->assign('move_posting', intval($_REQUEST['move_posting']));
					if ($field['pid'] == 0)
						$smarty->assign('posting_type', 0);
					else
						$smarty->assign('posting_type', 1);
				} else {
					$smarty->assign('no_authorisation', 'no_authorisation');
				}
				$smarty->assign('name', htmlspecialchars($field['name']));
				$smarty->assign('subject', htmlspecialchars($field['subject']));
				$smarty->assign('formated_time', format_time($lang['time_format_full'], $field['disp_time']));
				$smarty->assign('name_repl_subnav', htmlspecialchars($field['name']));
				$subnav_link = array(
					'mode' => $back,
					'id' => intval($_REQUEST['move_posting']),
					'title' => 'back_to_entry_link_title',
					'name' => 'back_to_entry_link'
				);
				$smarty->assign("back", $back);
				$smarty->assign("subnav_link", $subnav_link);
				$smarty->assign('subtemplate', 'posting_move.inc.tpl');
			} else {
				header('Location: index.php?mode=index');
				exit;
			}
			mysqli_free_result($move_result);
		}
		break;
	case 'manage_postings':
		if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) {
			// OK.
		} else {
			$smarty->assign('no_authorisation', 'no_authorisation');
		}
		$subnav_link = array(
			'mode' => 'index',
			'title' => 'back_to_index_link_title',
			'name' => 'back_to_index_link'
		);
		$smarty->assign("subnav_link", $subnav_link);
		$smarty->assign('subtemplate', 'posting_manage_postings.inc.tpl');
		break;
}
?>