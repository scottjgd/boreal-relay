=== Boreal Relay ===
Contributors: borealformstudio
Tags: customer service, chatbot, ai, openai, live chat
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 2.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A BYOK AI support assistant with approved answers, conversation history, feedback, and safe human handoff.

== Description ==

Boreal Relay adds an AI customer-support widget to WordPress. It uses your own OpenAI API key, answers with approved starter knowledge, records conversations and visitor feedback, and hands uncertain or sensitive questions to a person.

The free plugin is complete and useful without a licence:

* Customer-facing chat widget
* Your own OpenAI API key and choice of supported model
* 25+ approved starter answers for common support topics
* Conversation history in WordPress
* Helpful and unhelpful response feedback
* Human escalation with email notification and a local follow-up queue
* Bot name, greeting, colour, tone, business name, and support-email settings
* No Borealform account, licence check, telemetry, or background vendor request

= Optional Boreal Relay Pro add-on =

Boreal Relay Pro is a separately installed commercial extension for teams that want the assistant to improve around their real products, policies, and support workflow.

Pro adds:

* Create custom knowledge-base answers
* Edit, approve, pause, and delete knowledge entries
* Turn the first negative rating on an answer into a pending review draft
* Correct a draft before approving it for future answers
* Commercial updates and priority product support

Removing or deactivating Pro does not disable the free chat, history, feedback, escalation, or settings. Pro code and licence handling are not included in the WordPress.org download.

Learn more at https://borealform.com/boreal-relay

== Installation ==

1. Install and activate Boreal Relay from the WordPress Plugins screen.
2. Go to **Boreal Relay > Settings**.
3. Enter your own OpenAI API key and select a model.
4. Set the business name and support email used for human handoff.
5. Review the included answers under **Boreal Relay > Knowledge Base**.
6. Enable the widget when you are ready for visitors to use it.

No Borealform account or licence is required for the free plugin.

== External services ==

Boreal Relay connects to OpenAI only after a site administrator saves an OpenAI API key and enables the widget.

For each non-local chat response, the plugin sends the visitor's message, recent conversation messages, the current page URL, the configured business details, and approved knowledge-base text to OpenAI's Chat Completions API. OpenAI processes this data under the site owner's OpenAI account and settings.

Site owners are responsible for:

* reviewing OpenAI's terms and privacy practices;
* telling visitors that an external AI service processes chat messages;
* choosing an appropriate legal basis and retention policy; and
* avoiding sensitive personal information in approved knowledge or prompts.

OpenAI terms: https://openai.com/policies/terms-of-use/

OpenAI privacy policy: https://openai.com/policies/privacy-policy/

Disabling the widget stops visitor chat requests. Removing the saved OpenAI API key prevents OpenAI requests while keeping the local administration screens available.

The free plugin does not contact Borealform, perform licence checks, load remote code, or use an external updater.

== Privacy and local data ==

Boreal Relay stores conversations, response feedback, page URLs, escalation records, optional visitor contact details, and approved knowledge in the site's WordPress database. Escalation email uses the site's configured WordPress mail delivery.

Administrators can review conversations and escalations in WordPress. Deactivation retains data so reactivation does not erase the support record. Uninstalling the plugin deletes its three custom database tables and plugin options.

Site owners should update their privacy notice and establish a suitable retention process before enabling the widget.

== Frequently Asked Questions ==

= Does Free stop working after a trial? =

No. There is no trial timer, message quota, or licence requirement in the WordPress.org plugin. You continue to pay OpenAI directly for usage on your own account.

= What is the reason to buy Pro? =

Free gives visitors a working first line of support. Pro is for the team maintaining answer quality: it lets you replace generic starter guidance with exact products and policies, review failed answers, correct them, and approve better responses.

= Will it work with WooCommerce? =

The widget can appear on standard WordPress and WooCommerce pages. It does not read private order data or modify checkout.

= What happens when the assistant is not confident? =

It can ask the visitor for contact details, email the configured support address, and create a local escalation record for the team to resolve.

= Does the plugin train an OpenAI model on my website? =

No. Approved knowledge is added to each request as context. Boreal Relay does not fine-tune a model.

= Can Free users edit the included answers? =

Free users can review and use all included approved answers. Creating, editing, approving, pausing, and deleting knowledge entries are part of the separately installed Pro add-on.

== Screenshots ==

1. The Boreal Relay dashboard with conversation, message, escalation, and confidence summaries.
2. Widget settings for OpenAI, brand voice, greeting, colour, and human handoff.
3. The customer-facing chat widget with feedback and escalation.
4. Conversation history stored in the WordPress administration area.
5. The read-only included knowledge base in Free.
6. The separate Pro add-on editing and feedback-review workflow.

== Changelog ==

= 2.1.0 =

* Prepared the free edition for WordPress.org distribution.
* Moved commercial licence verification and knowledge editing into a separate Pro add-on.
* Kept chat, included knowledge, history, feedback, escalation, and settings permanently available in Free.
* Added explicit OpenAI external-service, privacy, local-data, and uninstall disclosures.
* Added a reproducible free-only packaging check that rejects Pro, licence, updater, hidden, and unexpected files.

= 2.0.0 =

* Independent release as Boreal Relay with a product-specific namespace and database tables.
* Added approved starter knowledge, BYOK OpenAI chat, conversation history, feedback, and human escalation.