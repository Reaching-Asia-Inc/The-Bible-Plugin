// Analytics utility for The Bible Plugin
// Attempts to fire GA4 (gtag), dataLayer, or Universal Analytics events if available.
// Exposes trackEvent and also window.tbpTrackEvent for non-module access.

export function trackEvent(name, params = {}) {
  const destinations = [];
  const errors = [];

  try {
    // GA4 via dataLayer (Preferred for debugging and GTM)
    if (typeof window !== 'undefined' && Array.isArray(window.dataLayer)) {
      try {
        window.dataLayer.push({
          event: name,
          ...(params || {})
        });
        destinations.push('dataLayer');
      } catch (e) {
        errors.push(`dataLayer: ${e.message}`);
      }
    }

    // GA4 via gtag
    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
      try {
        window.gtag('event', name, params || {});
        destinations.push('gtag');
      } catch (e) {
        errors.push(`gtag: ${e.message}`);
      }
    }

    // Universal Analytics fallback
    if (typeof window !== 'undefined' && typeof window.ga === 'function') {
      try {
        const label = (() => {
          try { return JSON.stringify(params || {}); } catch (e) { return String(params); }
        })();
        window.ga('send', 'event', 'BiblePlugin', name, label);
        destinations.push('ga');
      } catch (e) {
        errors.push(`ga: ${e.message}`);
      }
    }
  } catch (e) {
    errors.push(`General: ${e.message}`);
  }

  // Log to console and maintain a global list of events for debugging ("listing")
  if (typeof window !== 'undefined') {
    const timestamp = new Date().toISOString();

    // Add to global list
    window.tbpAnalyticsEvents = window.tbpAnalyticsEvents || [];
    window.tbpAnalyticsEvents.push({ name, params, timestamp, destinations, errors });

    // Console log
    const logPrefix = `[TBP Analytics]`;
    const sentTo = destinations.length > 0 ? `sent via: ${destinations.join(', ')}` : 'NOT SENT (no provider)';
    const logMethod = destinations.length > 0 ? 'log' : 'warn';

    console[logMethod](`${logPrefix} ${name} (${sentTo})`, params);
    if (errors.length > 0) {
      console.error(`${logPrefix} Errors:`, errors);
    }
  }

  return destinations.length > 0;
}

// Global verification helper
if (typeof window !== 'undefined') {
  window.tbpTrackEvent = trackEvent;
  window.tbpVerifyAnalytics = () => {
    console.group('TBP Analytics Verification');
    console.log('gtag function:', typeof window.gtag);
    console.log('dataLayer array:', Array.isArray(window.dataLayer));
    console.log('ga function:', typeof window.ga);
    console.log('Recent Events:', window.tbpAnalyticsEvents || []);
    console.log('To test an event, run: tbpTrackEvent("test_event", {status: "verifying"})');
    console.groupEnd();
    return true;
  };
}
