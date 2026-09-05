<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Knowledge_Base {

    // -----------------------------------------------------------------------
    // Seed
    // -----------------------------------------------------------------------

    public function seed_initial_knowledge() {
        global $wpdb;
        $table = $wpdb->prefix . 'boreal_relay_knowledge';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Seed detection reads this plugin's trusted custom table; its identifier is built from the WordPress prefix.
        $existing = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table . " WHERE source = 'seed'" );
        if ( intval( $existing ) > 0 ) {
            return;
        }

        $knowledge = array(
            array(
                'category' => 'overview',
                'question' => 'What does your business do?',
                'answer'   => 'We are a customer-focused business dedicated to providing quality products and services. We would love to tell you more — please visit our About page or use the Contact form to get in touch and our team will be happy to help.',
            ),
            array(
                'category' => 'overview',
                'question' => 'Can you tell me more about your company?',
                'answer'   => 'Thank you for your interest! Our team works hard to deliver a great experience for every customer. For full details about who we are and what we do, please visit our About page on the website.',
            ),
            array(
                'category' => 'location',
                'question' => 'Where are you located? What area do you serve?',
                'answer'   => 'Please visit our Contact page for our current location and service area details. Our team will be happy to confirm whether we can serve your specific area.',
            ),
            array(
                'category' => 'location',
                'question' => 'Do you serve my area? Are you available in my region?',
                'answer'   => 'Service availability can vary. Please use the Contact page on our website to reach our team and they can confirm coverage for your location.',
            ),
            array(
                'category' => 'products',
                'question' => 'What products or services do you offer?',
                'answer'   => 'We offer a range of products and services designed to meet different needs and budgets. You can browse everything available in the Shop or Services section of our website. If you have a specific need in mind, feel free to ask!',
            ),
            array(
                'category' => 'products',
                'question' => 'Do you offer any new products or services?',
                'answer'   => 'Our offerings are updated regularly. Please check the Shop or Services section of our website for the latest options, or contact us and our team will walk you through what is currently available.',
            ),
            array(
                'category' => 'ordering',
                'question' => 'How do I place an order or book a service?',
                'answer'   => 'You can place an order or make a booking directly through our website. Browse to find what you need, follow the on-screen steps to complete your request, and you will receive a confirmation. If you need assistance, our Contact page is always available.',
            ),
            array(
                'category' => 'ordering',
                'question' => 'Can I modify or cancel my order after placing it?',
                'answer'   => 'Order changes and cancellations depend on how far along your order has progressed. Please contact us through our Contact page as soon as possible with your order number and our team will do their best to help.',
            ),
            array(
                'category' => 'quotes',
                'question' => 'How do I get a quote or estimate?',
                'answer'   => 'For a personalized quote, please reach out through the Contact page on our website. Provide as much detail as you can about what you need and our team will get back to you promptly.',
            ),
            array(
                'category' => 'quotes',
                'question' => 'How much does it cost? What are your prices?',
                'answer'   => 'Pricing depends on the product or service selected. Please visit our Shop or Services section for current pricing, or contact us for a custom quote tailored to your needs.',
            ),
            array(
                'category' => 'billing',
                'question' => 'I have a question about my invoice or bill.',
                'answer'   => 'For any billing questions or discrepancies, please contact us through our Contact page with your order or invoice number. Our team will review and respond as quickly as possible.',
            ),
            array(
                'category' => 'payments',
                'question' => 'What payment methods do you accept?',
                'answer'   => 'We accept the major payment methods available through our secure checkout. Please proceed to checkout on our website to see all options, or contact us if you have a question about a specific payment method.',
            ),
            array(
                'category' => 'payments',
                'question' => 'Is your checkout secure? Is it safe to pay online?',
                'answer'   => 'Payment security depends on the checkout provider and settings used by this website. Please review the checkout details and the site’s Privacy Policy, or contact our team if you have a question before completing a payment.',
            ),
            array(
                'category' => 'delivery',
                'question' => 'How long does delivery or fulfilment take?',
                'answer'   => 'Delivery and fulfilment timelines depend on what you have ordered and your location. Please check the product or service listing for estimated timelines, or contact us if you have a specific deadline in mind.',
            ),
            array(
                'category' => 'delivery',
                'question' => 'How will my order be delivered or fulfilled?',
                'answer'   => 'Delivery or fulfilment details vary by product and service. You will receive information about this after placing your order. If you have specific questions before ordering, please use the Contact page.',
            ),
            array(
                'category' => 'returns',
                'question' => 'What is your return or cancellation policy?',
                'answer'   => 'Our return and cancellation policy is outlined on our website. If you need to return something or cancel a booking, please contact us as soon as possible through our Contact page with your order details and we will guide you through the process.',
            ),
            array(
                'category' => 'returns',
                'question' => 'My order arrived with a problem. What should I do?',
                'answer'   => 'We are sorry to hear that! Please contact us right away through our Contact page with your order number and a description of the issue — include photos if possible. We will prioritize making this right for you.',
            ),
            array(
                'category' => 'accounts',
                'question' => 'How do I create an account or log in?',
                'answer'   => 'You can create an account or sign in by clicking the "My Account" link in the navigation menu on our website. An account lets you track orders, view history, and manage your preferences.',
            ),
            array(
                'category' => 'accounts',
                'question' => 'How do I track my order?',
                'answer'   => 'To track your order, log into your account and visit the "My Account" or "Orders" section. Once your order is fulfilled, you will also receive a follow-up notification. If you need help, please contact us.',
            ),
            array(
                'category' => 'privacy',
                'question' => 'How do you handle my personal data and privacy?',
                'answer'   => 'We take privacy seriously. Please review our Privacy Policy on our website for full details about how we collect, use, and protect your information. If you have specific questions, feel free to contact us.',
            ),
            array(
                'category' => 'accessibility',
                'question' => 'Is your website or service accessible to everyone?',
                'answer'   => 'We are committed to making our website and services as accessible as possible. If you encounter any accessibility barriers or need specific accommodations, please contact us and we will do our best to assist you.',
            ),
            array(
                'category' => 'technical',
                'question' => 'I am having trouble using the website. Can you help?',
                'answer'   => 'We are sorry for any inconvenience! Please try refreshing the page or clearing your browser cache. If the problem continues, contact us through the Contact page and describe what you are experiencing — our team will look into it right away.',
            ),
            array(
                'category' => 'technical',
                'question' => 'The website is not loading correctly. What should I do?',
                'answer'   => 'Please try a different browser or device first. If the issue persists, let us know through our Contact page and our team will investigate.',
            ),
            array(
                'category' => 'contact',
                'question' => 'How can I contact you or get in touch with your team?',
                'answer'   => 'The best way to reach us is through the Contact page on our website. Fill out the contact form and our team will get back to you as soon as possible.',
            ),
            array(
                'category' => 'hours',
                'question' => 'What are your business hours or response times?',
                'answer'   => 'For our current business hours and typical response times, please check our Contact page. We aim to respond to all inquiries promptly and will get back to you as soon as we can.',
            ),
        );

        foreach ( $knowledge as $item ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This writes seed data to this plugin's trusted custom table with explicit formats.
            $wpdb->insert(
                $table,
                array(
                    'question' => $item['question'],
                    'answer'   => $item['answer'],
                    'category' => $item['category'],
                    'source'   => 'seed',
                    'approved' => 1,
                ),
                array( '%s', '%s', '%s', '%s', '%d' )
            );
        }
    }

    // -----------------------------------------------------------------------
    // Read — available to Free plan
    // -----------------------------------------------------------------------

    public function get_all( $approved_only = true ) {
        global $wpdb;
        $table = $wpdb->prefix . 'boreal_relay_knowledge';
        $sql = 'SELECT * FROM ' . $table . ( $approved_only ? ' WHERE approved = 1' : '' ) . ' ORDER BY category, id';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Knowledge entries must be current; this plugin-owned table identifier is trusted and the condition is a boolean-controlled literal.
        return $wpdb->get_results( $sql );
    }

    public function get_as_context() {
        $items = $this->get_all( true );
        if ( empty( $items ) ) return '';

        $context = "KNOWLEDGE BASE — use this to answer customer questions:\n\n";
        foreach ( $items as $item ) {
            $context .= 'Q: ' . $item->question . "\nA: " . $item->answer . "\n\n";
        }
        return $context;
    }

    // -----------------------------------------------------------------------
    // Write — Pro-only callers; sanitization happens here as a second layer.
    // -----------------------------------------------------------------------

    public function add_entry( $question, $answer, $category = 'general', $source = 'admin' ) {
        global $wpdb;

        // Source allowlist.
        $allowed_sources = array( 'seed', 'learned', 'admin' );
        if ( ! in_array( $source, $allowed_sources, true ) ) {
            $source = 'admin';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This writes to this plugin's trusted custom table with explicit formats.
        return $wpdb->insert(
            $wpdb->prefix . 'boreal_relay_knowledge',
            array(
                'question' => sanitize_text_field( $question ),
                'answer'   => sanitize_textarea_field( $answer ),
                'category' => sanitize_text_field( $category ),
                'source'   => $source,
                'approved' => ( $source === 'admin' ) ? 1 : 0,
            ),
            array( '%s', '%s', '%s', '%s', '%d' )
        );
    }

    /**
     * Update a KB entry with explicit format array.
     *
     * @param int   $id
     * @param array $data  Keys: question, answer, category, approved.
     * @return int|false
     */
    public function update_entry( $id, $data ) {
        global $wpdb;

        $sanitized = array();
        $formats   = array();

        if ( isset( $data['question'] ) ) {
            $sanitized['question'] = sanitize_text_field( $data['question'] );
            $formats[]             = '%s';
        }
        if ( isset( $data['answer'] ) ) {
            $sanitized['answer'] = sanitize_textarea_field( $data['answer'] );
            $formats[]           = '%s';
        }
        if ( isset( $data['category'] ) ) {
            $sanitized['category'] = sanitize_text_field( $data['category'] );
            $formats[]             = '%s';
        }
        if ( isset( $data['approved'] ) ) {
            $sanitized['approved'] = $data['approved'] ? 1 : 0;
            $formats[]             = '%d';
        }

        if ( empty( $sanitized ) ) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This updates this plugin's trusted custom table with explicit formats.
        return $wpdb->update(
            $wpdb->prefix . 'boreal_relay_knowledge',
            $sanitized,
            array( 'id' => intval( $id ) ),
            $formats,
            array( '%d' )
        );
    }

    public function delete_entry( $id ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- This deletes from this plugin's trusted custom table with an explicit ID format.
        return $wpdb->delete(
            $wpdb->prefix . 'boreal_relay_knowledge',
            array( 'id' => intval( $id ) ),
            array( '%d' )
        );
    }

    public function increment_use( $id ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This atomic counter update targets this plugin's trusted custom table; the ID is prepared.
        $wpdb->query( $wpdb->prepare(
            'UPDATE ' . $wpdb->prefix . 'boreal_relay_knowledge SET use_count = use_count + 1 WHERE id = %d',
            intval( $id )
        ) );
    }
}
