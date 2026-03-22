const AppLoader = () => {
  return (
    <div
      className="app-loader-overlay"
      role="status"
      aria-live="polite"
      aria-label="Loading Baby Shop KE"
    >
      <div className="app-loader-stack">
        <div className="app-loader-icon-shell">
          <img
            src="/favicon.ico"
            alt="Baby Shop KE favicon"
            className="app-loader-icon"
          />
        </div>
        <div className="app-loader-spinner" />
        <p className="app-loader-text">Loading Baby Shop KE...</p>
      </div>
    </div>
  );
};

export default AppLoader;
