--
-- Структура таблицы `z_ads`
--

CREATE TABLE IF NOT EXISTS `z_ads` (
  `ad_id` int(11) unsigned NOT NULL,
  `pars_id` int(11) NOT NULL,
  `avito_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  `t` int(11) NOT NULL,
  `t_creation` int(11) NOT NULL,
  `t_fix` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `z_ads_categories`
--

CREATE TABLE IF NOT EXISTS `z_ads_categories` (
  `ad_id` int(11) NOT NULL,
  `category_id_1` int(11) NOT NULL DEFAULT '0',
  `category_id_2` int(11) NOT NULL DEFAULT '0',
  `category_id_3` int(11) NOT NULL DEFAULT '0',
  `category_id_4` int(11) NOT NULL DEFAULT '0',
  `category_id_5` int(11) NOT NULL DEFAULT '0',
  `category_id_6` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `z_ads_info`
--

CREATE TABLE IF NOT EXISTS `z_ads_info` (
  `ad_id` int(11) NOT NULL,
  `info` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `z_ads_locations`
--

CREATE TABLE IF NOT EXISTS `z_ads_locations` (
  `ad_id` int(11) NOT NULL,
  `location_id_1` int(11) NOT NULL DEFAULT '0',
  `location_id_2` int(11) NOT NULL DEFAULT '0',
  `location_id_3` int(11) NOT NULL DEFAULT '0',
  `location_id_4` int(11) NOT NULL DEFAULT '0',
  `location_id_5` int(11) NOT NULL DEFAULT '0',
  `location_id_6` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `z_assholes`
--

CREATE TABLE IF NOT EXISTS `z_assholes` (
  `ass_id` int(11) NOT NULL,
  `u_a` text NOT NULL,
  `ip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `z_bots`
--

CREATE TABLE IF NOT EXISTS `z_bots` (
  `bot_id` int(11) NOT NULL,
  `bot_type_id` int(11) NOT NULL,
  `u_a` text NOT NULL,
  `title` varchar(200) NOT NULL,
  `descr` text NOT NULL,
  `ptr` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `z_bot_types`
--

CREATE TABLE IF NOT EXISTS `z_bot_types` (
  `bot_type_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `z_categories`
--

CREATE TABLE IF NOT EXISTS `z_categories` (
  `category_id` int(11) NOT NULL,
  `parent_category_id` int(11) NOT NULL,
  `url` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `info` text NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `z_locations`
--

CREATE TABLE IF NOT EXISTS `z_locations` (
  `location_id` int(11) NOT NULL,
  `parent_location_id` int(11) NOT NULL,
  `url` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `info` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `z_pars`
--

CREATE TABLE IF NOT EXISTS `z_pars` (
  `pars_id` int(11) unsigned NOT NULL,
  `ad_id` int(11) NOT NULL,
  `info` longblob NOT NULL,
  `meta_info` longblob NOT NULL,
  `t` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `z_uploads`
--

CREATE TABLE IF NOT EXISTS `z_uploads` (
  `upload_hash` varchar(64) NOT NULL,
  `info` longblob NOT NULL,
  `user_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `t` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `z_users`
--

CREATE TABLE IF NOT EXISTS `z_users` (
  `user_id` int(11) NOT NULL,
  `pass` varchar(200) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `t` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `z_users_ads`
--

CREATE TABLE IF NOT EXISTS `z_users_ads` (
  `user_ad_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `z_views`
--

CREATE TABLE IF NOT EXISTS `z_views` (
  `view_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `u_a` text NOT NULL,
  `t` int(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  `bot_id` int(11) NOT NULL,
  `http_response_code` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `z_ads`
--
ALTER TABLE `z_ads`
  ADD PRIMARY KEY (`ad_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `active` (`active`),
  ADD KEY `avito_id` (`avito_id`),
  ADD KEY `pars_id` (`pars_id`),
  ADD KEY `t` (`t`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `t_fix` (`t_fix`),
  ADD KEY `category_id_2` (`category_id`,`active`);

--
-- Индексы таблицы `z_ads_categories`
--
ALTER TABLE `z_ads_categories`
  ADD PRIMARY KEY (`ad_id`),
  ADD KEY `category_id_1` (`category_id_1`),
  ADD KEY `category_id_2` (`category_id_2`),
  ADD KEY `category_id_3` (`category_id_3`),
  ADD KEY `category_id_4` (`category_id_4`),
  ADD KEY `category_id_5` (`category_id_5`),
  ADD KEY `category_id_6` (`category_id_6`);

--
-- Индексы таблицы `z_ads_info`
--
ALTER TABLE `z_ads_info`
  ADD PRIMARY KEY (`ad_id`);

--
-- Индексы таблицы `z_ads_locations`
--
ALTER TABLE `z_ads_locations`
  ADD PRIMARY KEY (`ad_id`),
  ADD KEY `location_id_1` (`location_id_1`),
  ADD KEY `location_id_2` (`location_id_2`),
  ADD KEY `location_id_3` (`location_id_3`),
  ADD KEY `location_id_4` (`location_id_4`),
  ADD KEY `location_id_5` (`location_id_5`),
  ADD KEY `location_id_6` (`location_id_6`);

--
-- Индексы таблицы `z_assholes`
--
ALTER TABLE `z_assholes`
  ADD PRIMARY KEY (`ass_id`);

--
-- Индексы таблицы `z_bots`
--
ALTER TABLE `z_bots`
  ADD PRIMARY KEY (`bot_id`),
  ADD KEY `bot_type_id` (`bot_type_id`);

--
-- Индексы таблицы `z_bot_types`
--
ALTER TABLE `z_bot_types`
  ADD PRIMARY KEY (`bot_type_id`);

--
-- Индексы таблицы `z_categories`
--
ALTER TABLE `z_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Индексы таблицы `z_locations`
--
ALTER TABLE `z_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `parent_location_id` (`parent_location_id`),
  ADD KEY `url` (`url`),
  ADD KEY `title` (`title`);

--
-- Индексы таблицы `z_pars`
--
ALTER TABLE `z_pars`
  ADD PRIMARY KEY (`pars_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Индексы таблицы `z_uploads`
--
ALTER TABLE `z_uploads`
  ADD PRIMARY KEY (`upload_hash`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Индексы таблицы `z_users`
--
ALTER TABLE `z_users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `email` (`email`(191)),
  ADD KEY `hash` (`hash`),
  ADD KEY `tel` (`tel`);

--
-- Индексы таблицы `z_users_ads`
--
ALTER TABLE `z_users_ads`
  ADD PRIMARY KEY (`user_ad_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Индексы таблицы `z_views`
--
ALTER TABLE `z_views`
  ADD PRIMARY KEY (`view_id`),
  ADD KEY `ad_id` (`ad_id`),
  ADD KEY `bot_type_id` (`bot_id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `http_response_code` (`http_response_code`),
  ADD KEY `t` (`t`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `z_ads`
--
ALTER TABLE `z_ads`
  MODIFY `ad_id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `z_assholes`
--
ALTER TABLE `z_assholes`
  MODIFY `ass_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `z_bots`
--
ALTER TABLE `z_bots`
  MODIFY `bot_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `z_bot_types`
--
ALTER TABLE `z_bot_types`
  MODIFY `bot_type_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `z_pars`
--
ALTER TABLE `z_pars`
  MODIFY `pars_id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `z_users`
--
ALTER TABLE `z_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `z_users_ads`
--
ALTER TABLE `z_users_ads`
  MODIFY `user_ad_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `z_views`
--
ALTER TABLE `z_views`
  MODIFY `view_id` int(11) NOT NULL AUTO_INCREMENT;

