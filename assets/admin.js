jQuery(function ($) {
  // Only run if bulk page elements exist
  const $scanBtn = $("#kdr-iaa-scan-btn");
  if (!$scanBtn.length) return;

  const api = window.kdr_iaa_ajax || {};
  const ajaxUrl = api.ajax_url;
  const nonce = api.nonce;

  const $missing = $("#kdr-iaa-missing-count");
  const $gen = $("#kdr-iaa-generated-count");
  const $skip = $("#kdr-iaa-skipped-count");
  const $err = $("#kdr-iaa-errors-count");

  const $genBtn = $("#kdr-iaa-generate-btn");
  const $stopBtn = $("#kdr-iaa-stop-btn");
  const $resetBtn = $("#kdr-iaa-reset-btn");

  const $badge = $("#kdr-iaa-status-badge");
  const $fill = $("#kdr-iaa-progress-fill");
  const $pText = $("#kdr-iaa-progress-text");
  const $pPct = $("#kdr-iaa-progress-percent");
  const $log = $("#kdr-iaa-log");

  let state = {
    scanning: false,
    running: false,
    stopRequested: false,
    missingTotal: 0,
    processedTotal: 0,
    generatedTotal: 0,
    skippedTotal: 0,
    errorsTotal: 0,
    offset: 0,
  };

  function logLine(text) {
    const time = new Date().toLocaleTimeString();
    $log.prepend(`<div class="kdr-iaa-log-line"><strong>[${time}]</strong> ${escapeHtml(text)}</div>`);
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function setBadge(text) {
    $badge.text(text);
  }

  function setButtons({ scanDisabled, genDisabled, stopDisabled, resetDisabled }) {
    $scanBtn.prop("disabled", !!scanDisabled);
    $genBtn.prop("disabled", !!genDisabled);
    $stopBtn.prop("disabled", !!stopDisabled);
    $resetBtn.prop("disabled", !!resetDisabled);
  }

  function updateStats() {
    $missing.text(state.missingTotal ? state.missingTotal : "0");
    $gen.text(state.generatedTotal);
    $skip.text(state.skippedTotal);
    $err.text(state.errorsTotal);
  }

  function updateProgress() {
    const total = state.missingTotal || 0;
    const done = Math.min(state.processedTotal, total);
    const pct = total > 0 ? Math.round((done / total) * 100) : 0;

    $fill.css("width", pct + "%");

    $pText.text(
      total > 0
        ? `Processed ${done} of ${total} images missing alt text`
        : `Scan to begin.`
    );
    $pPct.text(pct + "%");
  }

  function resetSession() {
    state.running = false;
    state.stopRequested = false;
    state.processedTotal = 0;
    state.generatedTotal = 0;
    state.skippedTotal = 0;
    state.errorsTotal = 0;
    state.offset = 0;

    updateStats();
    updateProgress();
  }

  // --- AJAX helpers ---
  function post(action, data) {
    return $.post(ajaxUrl, Object.assign({ action, nonce }, data || {}));
  }

  // --- Scan ---
  $scanBtn.on("click", function () {
    if (state.scanning || state.running) return;

    if (!ajaxUrl || !nonce) {
      logLine("AJAX config missing. Make sure admin class localizes kdr_iaa_ajax.");
      return;
    }

    state.scanning = true;
    setBadge("Scanning...");
    setButtons({ scanDisabled: true, genDisabled: true, stopDisabled: true, resetDisabled: true });
    logLine("Scanning media library for images with missing alt text...");

    post("kdr_iaa_scan_missing", {})
      .done(function (res) {
        if (!res || !res.success) {
          logLine("Scan failed: " + (res?.data?.message || "Unknown error"));
          return;
        }

        state.missingTotal = parseInt(res.data.missing || 0, 10);
        state.offset = 0;
        resetSession(); // resets counters but keeps missingTotal
        updateStats();
        updateProgress();

        if (state.missingTotal > 0) {
          setBadge("Ready");
          setButtons({ scanDisabled: false, genDisabled: false, stopDisabled: true, resetDisabled: false });
          logLine(`Scan complete. Found ${state.missingTotal} images missing alt text.`);
        } else {
          setBadge("All set");
          setButtons({ scanDisabled: false, genDisabled: true, stopDisabled: true, resetDisabled: false });
          logLine("Scan complete. No missing alt text found.");
        }
      })
      .fail(function (xhr) {
        logLine("Scan request failed: " + (xhr?.statusText || "Network error"));
      })
      .always(function () {
        state.scanning = false;
      });
  });

  // --- Generate loop ---
  $genBtn.on("click", function () {
    if (state.running) return;
    if (state.missingTotal <= 0) return;

    // Confirm for large runs (nice UX)
    if (state.missingTotal >= 200) {
      const ok = window.confirm(`This will update alt text for ${state.missingTotal} images. Continue?`);
      if (!ok) return;
    }

    state.running = true;
    state.stopRequested = false;
    setBadge("Running...");
    setButtons({ scanDisabled: true, genDisabled: true, stopDisabled: false, resetDisabled: true });
    logLine("Bulk generation started.");

    runNextBatch();
  });

  function runNextBatch() {
    if (state.stopRequested) {
      state.running = false;
      setBadge("Stopped");
      setButtons({ scanDisabled: false, genDisabled: false, stopDisabled: true, resetDisabled: false });
      logLine("Stopped by user. You can resume by clicking Generate again.");
      return;
    }

    post("kdr_iaa_process_batch", { offset: state.offset })
      .done(function (res) {
        if (!res || !res.success) {
          state.running = false;
          setBadge("Error");
          setButtons({ scanDisabled: false, genDisabled: false, stopDisabled: true, resetDisabled: false });
          logLine("Batch failed: " + (res?.data?.message || "Unknown error"));
          return;
        }

        const d = res.data || {};
        const processed = parseInt(d.processed || 0, 10);
        const generated = parseInt(d.generated || 0, 10);
        const skipped = parseInt(d.skipped || 0, 10);
        const errors = parseInt(d.errors || 0, 10);

        state.processedTotal += processed;
        state.generatedTotal += generated;
        state.skippedTotal += skipped;
        state.errorsTotal += errors;

        state.offset = parseInt(d.next_offset || state.offset, 10);

        updateStats();
        updateProgress();

        // Log occasionally (avoid spamming)
        logLine(`Batch done: +${generated} generated, +${skipped} skipped, +${errors} errors.`);

        if (d.done) {
          state.running = false;
          setBadge("Completed");
          setButtons({ scanDisabled: false, genDisabled: false, stopDisabled: true, resetDisabled: false });
          logLine("Bulk generation completed.");
          return;
        }

        // Continue quickly but not too aggressive
        window.setTimeout(runNextBatch, 150);
      })
      .fail(function (xhr) {
        state.running = false;
        setBadge("Error");
        setButtons({ scanDisabled: false, genDisabled: false, stopDisabled: true, resetDisabled: false });
        logLine("Batch request failed: " + (xhr?.statusText || "Network error"));
      });
  }

  // --- Stop ---
  $stopBtn.on("click", function () {
    if (!state.running) return;
    state.stopRequested = true;
    setBadge("Stopping...");
    logLine("Stop requested. Finishing current batch...");
  });

  // --- Reset session ---
  $resetBtn.on("click", function () {
    if (state.running || state.scanning) return;
    resetSession();
    setBadge(state.missingTotal > 0 ? "Ready" : "Idle");
    setButtons({
      scanDisabled: false,
      genDisabled: state.missingTotal <= 0,
      stopDisabled: true,
      resetDisabled: false,
    });
    logLine("Session reset.");
  });

  // Init default UI
  setBadge("Idle");
  setButtons({ scanDisabled: false, genDisabled: true, stopDisabled: true, resetDisabled: true });
  updateStats();
  updateProgress();
});



// ===== Settings Page Live Preview =====
jQuery(function ($) {
  const $previewInput = $("#kdr-iaa-preview-filename");
  if (!$previewInput.length) return;

  const $out = $("#kdr-iaa-preview-output");

  function cleanName(filename, settings){
    // mimic PHP generator rules (client-side preview)
    let name = (filename || "").trim();
    name = name.replace(/^.*[\\\/]/, ""); // remove path
    name = name.replace(/\.[^.]+$/, ""); // remove extension
    name = name.replace(/[-_]+/g, " ").trim();

    if (settings.remove_prefixes) {
      name = name.replace(/\b(img|dsc|pxl|wp|photo)\s*/ig, "");
      name = name.trim();
    }

    name = name.replace(/[^a-z0-9 ]+/ig, " ");
    name = name.replace(/\s+/g, " ").trim();

    if (settings.skip_numeric && /^\d+$/.test(name)) return "";

    if (settings.case_mode === "title") {
      name = name.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());
    } else {
      name = name.toLowerCase();
      name = name.charAt(0).toUpperCase() + name.slice(1);
    }

    return name;
  }

  function getSettingsFromForm(){
    const caseMode = $("#case_mode").val() || "sentence";
    const removePrefixes = $("input[name='remove_prefixes']").is(":checked");
    const skipNumeric = $("input[name='skip_numeric']").is(":checked");
    return {
      case_mode: caseMode,
      remove_prefixes: removePrefixes,
      skip_numeric: skipNumeric
    };
  }

  function render(){
    const s = getSettingsFromForm();
    const filename = $previewInput.val();
    const result = cleanName(filename, s);
    $out.text(result || "— (skipped by rules)");
  }

  // Live updates
  $previewInput.on("input", render);
  $("#case_mode").on("change", render);
  $("input[name='remove_prefixes'], input[name='skip_numeric']").on("change", render);

  render();
});
