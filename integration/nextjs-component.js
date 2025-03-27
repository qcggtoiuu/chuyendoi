import { useEffect, useState } from 'react';

/**
 * ChuyenDoiTracker - A component for tracking visitor information and displaying interactive buttons
 * 
 * @param {Object} props Component props
 * @param {string} props.apiKey API key for the tracking system
 * @param {string} props.style Button style ('fab' or 'bar')
 * @param {string} props.phone Phone number for the phone button
 * @param {string} props.zalo Zalo link
 * @param {string} props.messenger Messenger link
 * @param {string} props.maps Google Maps link
 * @param {boolean} props.showLabels Whether to show labels on buttons
 * @param {string} props.primaryColor Primary color for buttons
 * @param {boolean} props.animation Whether to enable button animation
 * @param {boolean} props.debug Whether to enable debug logging
 * @returns {JSX.Element} The ChuyenDoiTracker component
 */
export default function ChuyenDoiTracker({
  apiKey,
  style = 'fab',
  phone = '',
  zalo = '',
  messenger = '',
  maps = '',
  showLabels = true,
  primaryColor = '#3961AA',
  animation = true,
  debug = false
}) {
  const [isLoaded, setIsLoaded] = useState(false);
  const [hideButtons, setHideButtons] = useState(false);
  const [embedCode, setEmbedCode] = useState('');
  
  useEffect(() => {
    // Load tracking script
    const script = document.createElement('script');
    script.src = 'https://chuyendoi.io.vn/assets/js/chuyendoi-track.js';
    script.async = true;
    script.onload = function() {
      setIsLoaded(true);
      
      // Initialize tracker if needed
      if (typeof window.ChuyenDoi !== 'undefined') {
        // Check if buttons should be hidden
        setHideButtons(window.ChuyenDoi.shouldHideButtons());
      }
    };
    
    // Add data attributes to script
    script.setAttribute('data-api-key', apiKey);
    if (debug) script.setAttribute('data-debug', 'true');
    if (phone) script.setAttribute('data-phone', phone);
    if (zalo) script.setAttribute('data-zalo', zalo);
    if (messenger) script.setAttribute('data-messenger', messenger);
    if (maps) script.setAttribute('data-maps', maps);
    if (style) script.setAttribute('data-style', style);
    if (showLabels === false) script.setAttribute('data-show-labels', 'false');
    if (primaryColor) script.setAttribute('data-primary-color', primaryColor);
    if (animation === false) script.setAttribute('data-animation', 'false');
    
    document.head.appendChild(script);
    
    // Load CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://chuyendoi.io.vn/assets/css/buttons.css';
    document.head.appendChild(link);
    
    // Fetch embed code
    fetch(`https://chuyendoi.io.vn/button_preview.php?api_key=${apiKey}&style=${style}&phone=${encodeURIComponent(phone)}&zalo=${encodeURIComponent(zalo)}&messenger=${encodeURIComponent(messenger)}&maps=${encodeURIComponent(maps)}&show_labels=${showLabels ? '1' : '0'}&primary_color=${encodeURIComponent(primaryColor)}&animation=${animation ? '1' : '0'}`)
      .then(response => response.text())
      .then(html => {
        // Extract the button HTML from the response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const previewContainer = doc.querySelector('.preview-container');
        
        if (previewContainer) {
          setEmbedCode(previewContainer.innerHTML);
        }
      })
      .catch(error => {
        console.error('Error fetching embed code:', error);
      });
    
    return () => {
      // Cleanup
      if (script.parentNode) {
        script.parentNode.removeChild(script);
      }
      if (link.parentNode) {
        link.parentNode.removeChild(link);
      }
    };
  }, [apiKey, style, phone, zalo, messenger, maps, showLabels, primaryColor, animation, debug]);
  
  // Don't render anything if buttons should be hidden
  if (hideButtons) {
    return null;
  }
  
  // Don't render anything if not loaded yet
  if (!isLoaded || !embedCode) {
    return null;
  }
  
  return (
    <div dangerouslySetInnerHTML={{ __html: embedCode }} />
  );
}

/**
 * Example usage:
 * 
 * import ChuyenDoiTracker from '../components/ChuyenDoiTracker';
 * 
 * export default function Layout({ children }) {
 *   return (
 *     <>
 *       {children}
 *       <ChuyenDoiTracker
 *         apiKey="your-api-key"
 *         phone="0916152929"
 *         zalo="https://zalo.me/0916152929"
 *         messenger="https://m.me/dienmaytotvietnam"
 *         maps="https://goo.gl/maps/Z4pipWWc1GW2aY6p8"
 *       />
 *     </>
 *   );
 * }
 */
