#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const scriptDir = path.dirname( fileURLToPath( import.meta.url ) );
const pluginDir = path.resolve( scriptDir, '..' );
const slug = 'boreal-relay';
const outputDir = path.join( pluginDir, '.wordpressorg-dist' );
const stagingRoot = path.join( outputDir, slug );
const zipPath = path.join( outputDir, `${ slug }.zip` );

fs.rmSync( outputDir, { recursive: true, force: true } );
fs.mkdirSync( stagingRoot, { recursive: true } );

function copyFile( relativePath ) {
    const destination = path.join( stagingRoot, relativePath );
    fs.mkdirSync( path.dirname( destination ), { recursive: true } );
    fs.copyFileSync( path.join( pluginDir, relativePath ), destination );
}

function copyDirectory( relativePath ) {
    fs.cpSync( path.join( pluginDir, relativePath ), path.join( stagingRoot, relativePath ), {
        recursive: true,
    } );
}

for ( const relativePath of [
    'boreal-relay.php',
    'readme.txt',
    'LICENSE',
    'uninstall.php',
] ) {
    copyFile( relativePath );
}
for ( const relativePath of [ 'includes', 'public', 'admin' ] ) {
    copyDirectory( relativePath );
}

execFileSync( 'zip', [ '-qr', zipPath, slug ], { cwd: outputDir, stdio: 'inherit' } );

const entries = execFileSync( 'unzip', [ '-Z1', zipPath ], { encoding: 'utf8' } )
    .split( '\n' )
    .filter( Boolean );

const allowedFiles = new Set( [
    `${ slug }/boreal-relay.php`,
    `${ slug }/readme.txt`,
    `${ slug }/LICENSE`,
    `${ slug }/uninstall.php`,
    `${ slug }/includes/class-ai-engine.php`,
    `${ slug }/includes/class-conversation.php`,
    `${ slug }/includes/class-database.php`,
    `${ slug }/includes/class-escalation.php`,
    `${ slug }/includes/class-knowledge-base.php`,
    `${ slug }/public/class-widget.php`,
    `${ slug }/public/css/chat-widget.css`,
    `${ slug }/public/js/chat-widget.js`,
    `${ slug }/admin/class-admin.php`,
    `${ slug }/admin/css/admin.css`,
    `${ slug }/admin/js/admin.js`,
    `${ slug }/admin/views/conversation-detail.php`,
    `${ slug }/admin/views/conversations.php`,
    `${ slug }/admin/views/dashboard.php`,
    `${ slug }/admin/views/escalations.php`,
    `${ slug }/admin/views/knowledge.php`,
    `${ slug }/admin/views/settings.php`,
] );

for ( const entry of entries ) {
    if ( entry.endsWith( '/' ) ) {
        continue;
    }
    if ( ! allowedFiles.has( entry ) ) {
        throw new Error( `Submission ZIP contains an unexpected path: ${ entry }` );
    }
}

const forbiddenSource = [
    { label: 'Borealform licence endpoint', expression: /api\.borealform\.com|\/v1\/licenses\//i },
    { label: 'Pro licence implementation', expression: /\bBRP?_License\b|boreal_relay_pro_license/i },
    { label: 'commercial updater hook', expression: /(?:pre_set|set)_site_transient_update_plugins/ },
    { label: 'remote code loading', expression: /file_get_contents\s*\(\s*['"]https?:\/\// },
    { label: 'Pro plugin bootstrap', expression: /Plugin Name:\s*Boreal Relay Pro/i },
];

for ( const entry of entries.filter( ( item ) => /\.(?:php|js)$/.test( item ) ) ) {
    const contents = execFileSync( 'unzip', [ '-p', zipPath, entry ], { encoding: 'utf8' } );
    for ( const rule of forbiddenSource ) {
        if ( rule.expression.test( contents ) ) {
            throw new Error( `Submission ZIP contains ${ rule.label } in ${ entry }.` );
        }
    }
}

const mainPlugin = execFileSync(
    'unzip',
    [ '-p', zipPath, `${ slug }/boreal-relay.php` ],
    { encoding: 'utf8' }
);
for ( const expectedHeader of [
    'Plugin Name: Boreal Relay',
    'Version: 2.1.0',
    'Text Domain: boreal-relay',
] ) {
    if ( ! mainPlugin.includes( expectedHeader ) ) {
        throw new Error( `Submission ZIP main plugin header is missing: ${ expectedHeader }` );
    }
}

const readme = fs.readFileSync( path.join( pluginDir, 'readme.txt' ), 'utf8' );
if ( ! /Stable tag:\s*2\.1\.0/.test( readme ) ) {
    throw new Error( 'readme.txt stable tag does not match version 2.1.0.' );
}
const shortDescription = readme.split( '\n' )[ 10 ]?.trim() ?? '';
if ( shortDescription.length > 150 ) {
    throw new Error( `WordPress.org short description is ${ shortDescription.length } characters.` );
}

console.log( `Created ${ path.relative( process.cwd(), zipPath ) }` );