/* Boreal Relay Chat Widget */
(function($) {
    'use strict';

    var BorealRelayChat = {
        sessionId: '',
        isOpen: false,
        messageCount: 0,
        hasEscalated: false,
        lastBotMessageId: null,
        lastUserMessage: '',

        init: function() {
            this.syncViewport();
            this.sessionId = this.getOrCreateSession();
            this.bindEvents();
            this.showGreeting();
            this.applyTheme();

            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', this.syncViewport);
                window.visualViewport.addEventListener('scroll', this.syncViewport);
            } else {
                window.addEventListener('resize', this.syncViewport);
            }

            // Show teaser label after 3s, hide after 9s
            setTimeout(function() {
                if (!BorealRelayChat.isOpen) {
                    $('#boreal-relay-teaser').addClass('visible');
                }
            }, 3000);
            setTimeout(function() {
                $('#boreal-relay-teaser').removeClass('visible');
            }, 9000);

            setTimeout(function() {
                BorealRelayChat.showBadge();
            }, 10000);
        },

        syncViewport: function() {
            var viewportHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight;
            document.documentElement.style.setProperty('--br-viewport-height', viewportHeight + 'px');
        },

        applyTheme: function() {
            var color = BorealRelay.theme_color || '#2563eb';
            document.documentElement.style.setProperty('--br-primary', color);
        },

        getOrCreateSession: function() {
            var key = 'boreal_relay_session';
            var existing = sessionStorage.getItem(key);
            if (existing) return existing;
            var id = 'br_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem(key, id);
            return id;
        },

        bindEvents: function() {
            $('#boreal-relay-toggle').on('click', function() { BorealRelayChat.toggle(); });
            $('#boreal-relay-teaser').on('click keypress', function(e) {
                if (e.type === 'click' || e.which === 13) { BorealRelayChat.open(); }
            });
            $('#boreal-relay-minimize').on('click', function() { BorealRelayChat.close(); });
            $('#boreal-relay-send').on('click', function() { BorealRelayChat.sendMessage(); });
            $('#boreal-relay-input').on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    BorealRelayChat.sendMessage();
                }
            });
            $('#boreal-relay-input').on('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 80) + 'px';
            });
        },

        toggle: function() {
            if (this.isOpen) { this.close(); } else { this.open(); }
        },

        open: function() {
            this.isOpen = true;
            $('#boreal-relay-teaser').removeClass('visible');
            $('#boreal-relay-panel').addClass('open');
            $('#boreal-relay-icon-chat').hide();
            $('#boreal-relay-icon-close').show();
            $('#boreal-relay-badge').hide().text('');
            $('#boreal-relay-toggle').removeClass('has-badge');
            $('#boreal-relay-input').focus();
            this.scrollToBottom();
        },

        close: function() {
            this.isOpen = false;
            $('#boreal-relay-panel').removeClass('open');
            $('#boreal-relay-icon-chat').show();
            $('#boreal-relay-icon-close').hide();
        },

        showBadge: function() {
            if (!this.isOpen && this.messageCount === 0) {
                $('#boreal-relay-badge').text('1').show();
                $('#boreal-relay-toggle').addClass('has-badge');
            }
        },

        showGreeting: function() {
            this.addBotMessage(BorealRelay.greeting || 'Hi! How can I help you today?');
        },

        isEscalationRequest: function(text) {
            var patterns = [
                /speak\s+(to|with)\s+(a\s+)?(person|human|someone|agent|rep|staff|team)/i,
                /talk\s+(to|with)\s+(a\s+)?(person|human|someone|agent|rep|staff|team)/i,
                /someone\s+else/i,
                /can\s+i\s+speak/i,
                /let\s+me\s+speak/i,
                /real\s+(person|human|agent)/i,
                /transfer\s+me/i,
                /connect\s+me\s+(to|with)/i,
                /get\s+a\s+(human|person|agent)/i,
                /prefer\s+(a\s+)?(human|person)/i,
                /human\s+(agent|support|help)/i,
            ];
            for (var i = 0; i < patterns.length; i++) {
                if (patterns[i].test(text)) return true;
            }
            return false;
        },

        sendMessage: function() {
            var text = $('#boreal-relay-input').val().trim();
            if (!text) return;

            this.lastUserMessage = text;
            $('#boreal-relay-input').val('').css('height', '');
            $('#boreal-relay-send').prop('disabled', true);
            this.messageCount++;

            this.addUserMessage(text);
            this.showTyping();

            $.ajax({
                url: BorealRelay.ajax_url,
                type: 'POST',
                data: {
                    action: 'boreal_relay_chat',
                    nonce: BorealRelay.nonce,
                    message: text,
                    session_id: this.sessionId,
                    page_url: BorealRelay.page_url || window.location.href,
                },
                success: function(res) {
                    BorealRelayChat.hideTyping();
                    $('#boreal-relay-send').prop('disabled', false);
                    if (res.success) {
                        var showFb = !res.data.api_error;
                        var msgId = BorealRelayChat.addBotMessage(res.data.reply, showFb, res.data.msg_db_id);
                        BorealRelayChat.lastBotMessageId = msgId;
                        var shouldEscalate = (res.data.escalate && !res.data.api_error) ||
                                             BorealRelayChat.isEscalationRequest(text);
                        if (shouldEscalate && !BorealRelayChat.hasEscalated) {
                            BorealRelayChat.hasEscalated = true;
                            setTimeout(function() {
                                BorealRelayChat.showContactForm();
                            }, 900);
                        }
                    } else {
                        BorealRelayChat.addBotMessage("I'm having trouble right now. Please use the Contact page on our website and we'll help you right away!");
                    }
                },
                error: function() {
                    BorealRelayChat.hideTyping();
                    $('#boreal-relay-send').prop('disabled', false);
                    BorealRelayChat.addBotMessage("Something went wrong on my end. Please try again or use the Contact page on our website to reach us directly.");
                },
            });
        },

        addUserMessage: function(text) {
            var time = this.formatTime(new Date());
            var html = '<div class="br-msg user">' +
                '<div class="br-bubble">' + this.escapeHtml(text) + '</div>' +
                '<span class="br-time">' + time + '</span>' +
                '</div>';
            $('#boreal-relay-messages').append(html);
            this.scrollToBottom();
        },

        addBotMessage: function(text, showFeedback, dbId) {
            var id = 'br-msg-' + Date.now();
            var time = this.formatTime(new Date());
            var feedback = '';
            if (showFeedback) {
                feedback = '<div class="br-feedback" id="fb-' + id + '">' +
                    '<button class="br-fb-yes" data-id="' + id + '" title="This was helpful">👍 Helpful</button>' +
                    '<button class="br-fb-no" data-id="' + id + '" title="This wasn\'t helpful">👎 Not helpful</button>' +
                    '</div>';
            }
            var html = '<div class="br-msg bot" id="' + id + '" data-db-id="' + (dbId || 0) + '">' +
                '<div class="br-bubble">' + this.linkify(text) + '</div>' +
                '<span class="br-time">' + time + '</span>' +
                feedback +
                '</div>';
            $('#boreal-relay-messages').append(html);
            this.scrollToBottom();
            this.bindFeedback(id, text);
            return id;
        },

        bindFeedback: function(id, replyText) {
            var self = this;
            $('#fb-' + id + ' .br-fb-yes').on('click', function() {
                $(this).addClass('active');
                $('#fb-' + id).find('button').prop('disabled', true);
                var dbId = $('#' + id).data('db-id');
                self.sendFeedback(dbId, true, self.lastUserMessage, replyText);
            });
            $('#fb-' + id + ' .br-fb-no').on('click', function() {
                $(this).addClass('active');
                $('#fb-' + id).find('button').prop('disabled', true);
                var dbId = $('#' + id).data('db-id');
                self.sendFeedback(dbId, false, self.lastUserMessage, replyText);
            });
        },

        sendFeedback: function(msgId, helpful, question, answer) {
            $.post(BorealRelay.ajax_url, {
                action: 'boreal_relay_feedback',
                nonce: BorealRelay.nonce,
                session_id: this.sessionId,
                message_id: msgId,
                helpful: helpful ? '1' : '0',
                question: question,
                answer: answer,
            });
        },

        showContactForm: function() {
            var $form = $('#boreal-relay-contact-form');
            $form.slideDown(250);
            BorealRelayChat.scrollToBottom();

            $('#boreal-relay-contact-submit').off('click').on('click', function() {
                var name  = $('#boreal-relay-contact-name').val().trim();
                var email = $('#boreal-relay-contact-email').val().trim();
                var phone = $('#boreal-relay-contact-phone').val().trim();

                if (!email && !phone) {
                    $('#boreal-relay-contact-email').focus();
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).text('Sending…');

                $.ajax({
                    url: BorealRelay.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'boreal_relay_save_contact',
                        nonce: BorealRelay.nonce,
                        session_id: BorealRelayChat.sessionId,
                        name: name,
                        email: email,
                        phone: phone,
                    },
                    success: function(res) {
                        if (res.success) {
                            $form.find('input, button').hide();
                            $('#boreal-relay-contact-intro').hide();
                            $('#boreal-relay-contact-thanks').show();
                        } else {
                            $btn.prop('disabled', false).text('Send My Info');
                            $btn.after('<p style="color:#dc2626;font-size:12px;margin:4px 0 0;">Something went wrong — please try again.</p>');
                        }
                        BorealRelayChat.scrollToBottom();
                    },
                    error: function() {
                        $btn.prop('disabled', false).text('Send My Info');
                        $btn.after('<p style="color:#dc2626;font-size:12px;margin:4px 0 0;">Could not connect — please use our Contact page directly.</p>');
                        BorealRelayChat.scrollToBottom();
                    },
                });
            });
        },

        showTyping: function() {
            var html = '<div class="br-msg bot" id="boreal-relay-typing">' +
                '<div class="br-typing"><span></span><span></span><span></span></div>' +
                '</div>';
            $('#boreal-relay-messages').append(html);
            this.scrollToBottom();
        },

        hideTyping: function() {
            $('#boreal-relay-typing').remove();
        },

        scrollToBottom: function() {
            var el = document.getElementById('boreal-relay-messages');
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatTime: function(date) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        },

        linkify: function(text) {
            text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            text = text.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
            text = text.replace(/([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/g, '<a href="mailto:$1">$1</a>');
            text = text.replace(/\n/g, '<br>');
            return text;
        },
    };

    $(document).ready(function() {
        BorealRelayChat.init();
    });

})(jQuery);
