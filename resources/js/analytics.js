// Analytics utility for The Bible Plugin
// Attempts to fire GA4 (gtag), dataLayer, or Universal Analytics events if available.
// Exposes trackEvent and also window.tbpTrackEvent for non-module access.

export function trackEvent(name, params = {}) {
  try {
    // GA4 via gtag
    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
      window.gtag('event', name, params || {});
      return true;
    }
    // GA4 via dataLayer
    if (typeof window !== 'undefined' && Array.isArray(window.dataLayer)) {
      window.dataLayer.push({
        event: name,
        ...(params || {})
      });
      return true;
    }
    // Universal Analytics fallback
    if (typeof window !== 'undefined' && typeof window.ga === 'function') {
      const label = (() => {
        try { return JSON.stringify(params || {}); } catch (e) { return String(params); }
      })();
      window.ga('send', 'event', 'BiblePlugin', name, label);
      return true;
    }
  } catch (e) {
    // swallow
  }
  return false;
}

// Optional: expose globally for inline scripts or non-module contexts
if (typeof window !== 'undefined') {
  window.tbpTrackEvent = trackEvent;
}
