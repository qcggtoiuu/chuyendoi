import { useEffect, useState, FC } from 'react';

/**
 * Props for the ChuyenDoiTracker component
 */
interface ChuyenDoiTrackerProps {
  /** API key for the tracking system */
  apiKey: string;
  /** Button style ('fab' or 'bar') */
  style?: 'fab' | 'bar';
  /** Phone number for the phone button */
  phone?: string;
  /** Zalo link */
  zalo?: string;
  /** Messenger link */
  messenger?: string;
  /** Google Maps link */
  maps?: string;
  /** Whether to show labels on buttons */
  showLabels?: boolean;
  /** Primary color for buttons */
  primaryColor?: string;
  /** Whether to enable button animation */
  animation?: boolean;
  /** Whether to enable debug logging */
  debug?: boolean;
}

/**
 * Declare global Tracker interface for TypeScript
 */
interface Tracker {
  init: (options: {
    apiKey: string;
    apiUrl: string;
    buttonSelector: string;
    debug: boolean;
  }) => void;
  shouldHideButtons: () => boolean;
  trackClick: (element: HTMLElement) => void;
  trackEvent: (eventName: string, eventData: any) => void;
  isBot: () => boolean;
}

declare global {
  interface Window {
    Tracker?: Tracker;
  }
}

/**
 * ChuyenDoiTracker - A component for tracking visitor information and displaying interactive buttons
 * Compatible with Vite + React + TypeScript projects
 */
const ChuyenDoiTracker: FC<ChuyenDoiTrackerProps> = ({
  apiKey,
  style,
  phone,
  zalo,
  messenger,
  maps,
  showLabels,
  primaryColor,
  animation,
  debug
}) => {
  const [isLoaded, setIsLoaded] = useState<boolean>(false);
  const [hideButtons, setHideButtons] = useState<boolean>(false);
  const [embedCode, setEmbedCode] = useState<string>('');
  
  useEffect(() => {
    // Load tracking script
    const script = document.createElement('script');
    script.src = 'https://chuyendoi.io.vn/assets/js/tracker.js';
    script.async = true;
    script.onload = function() {
      setIsLoaded(true);
      
      // Initialize tracker
      if (typeof window.Tracker !== 'undefined') {
        window.Tracker.init({
          apiKey: apiKey,
          apiUrl: 'https://chuyendoi.io.vn/api/track.php',
          buttonSelector: '.fab-wrapper, .bbas-pc-contact-bar',
          debug: debug
        });
        
        // Check if buttons should be hidden
        setHideButtons(window.Tracker.shouldHideButtons());
      }
    };
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
      document.head.removeChild(script);
      document.head.removeChild(link);
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
};

export default ChuyenDoiTracker;

/**
 * Example usage in a Vite + React + TypeScript project:
 * 
 * import ChuyenDoiTracker from './components/ChuyenDoiTracker';
 * 
 * const App: React.FC = () => {
 *   return (
 *     <div className="App">
 *       <h1>My Vite + React + TypeScript App</h1>
 *       <ChuyenDoiTracker
 *         apiKey="your-api-key"
 *         phone="0916152929"
 *         zalo="https://zalo.me/0916152929"
 *         messenger="https://m.me/dienmaytotvietnam"
 *         maps="https://goo.gl/maps/Z4pipWWc1GW2aY6p8"
 *       />
 *     </div>
 *   );
 * }
 * 
 * export default App;
 */
