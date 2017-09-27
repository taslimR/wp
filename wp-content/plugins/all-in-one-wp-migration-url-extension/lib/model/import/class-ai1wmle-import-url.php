<?php
/**
 * Copyright (C) 2014-2017 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

class Ai1wmle_Import_Url {

	public static function execute( $params, $client = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Creating an empty archive...', AI1WMLE_PLUGIN_NAME ) );

		// Set domain
		$domain = parse_url( $params['fileUrl'], PHP_URL_HOST );

		// Rewrite URLs
		switch ( $domain ) {
			case '1drv.ms':
			case 'onedrive.live.com':

				$matches = array();

				// Extract ID from the URL
				if ( preg_match( '/\/u\/(.+?)(\/|$)/', $params['fileUrl'], $matches ) ) {
					$params['fileUrl'] = sprintf( 'https://api.onedrive.com/v1.0/shares/%s/root/content', $matches[1] );
				}

				// Set URL client
				if ( empty( $client ) ) {
					$client = new ServMaskURLClient( $params['fileUrl'] );
				}

				// Get file meta and convert it to lowercase
				$meta = array_change_key_case( $client->getFileMeta() );

				// Set the exact file location
				$params['fileUrl'] = $meta['location'];

				break;

			case 'googledrive.com':
			case 'drive.google.com':
			case 'www.googledrive.com':

				$query = array();

				// Parse the query parameters from the URL
				parse_str( parse_url( $params['fileUrl'], PHP_URL_QUERY ), $query );

				// Determine if the query contains ID
				if ( isset( $query['id'] ) ) {

					// Create a download link for the Google Drive file
					$params['fileUrl'] = sprintf( 'https://www.googledrive.com/host/%s', $query['id'] );
				} else {
					$matches = array();

					// Extract ID from the URL
					if ( preg_match( '/\/d\/(.+?)(\/|$)/', $params['fileUrl'], $matches ) ) {
						$params['fileUrl'] = sprintf( 'https://www.googledrive.com/host/%s', $matches[1] );
					}
				}

				break;
		}

		// Set URL client
		if ( empty( $client ) ) {
			$client = new ServMaskURLClient( $params['fileUrl'] );
		}

		// Get file meta and convert it to lowercase
		$meta = array_change_key_case( $client->getFileMeta() );

		// Set file size
		if ( isset( $meta['accept-ranges'] ) && isset( $meta['content-length'] ) ) {
			if ( $meta['accept-ranges'] == 'bytes' ) {
				$params['totalBytes'] = $meta['content-length'];
			} else {
				$params['totalBytes'] = 0;
			}
		} else {
			$params['totalBytes'] = 0;
		}

		// Set file range
		if ( isset( $meta['accept-ranges'] ) ) {
			if ( $meta['accept-ranges'] !== 'bytes' ) {
				$params['totalBytes'] = 0;
			}
		}

		// Create empty archive file
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );
		$archive->close();

		// Set progress
		Ai1wm_Status::info( __( 'Done creating an empty archive.', AI1WMLE_PLUGIN_NAME ) );

		return $params;
	}
}
