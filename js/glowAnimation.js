/**
 * glowAnimation.js
 *
 * This script provides a reusable function to create a chasing glow effect
 * around any specified HTML element using an HTML5 Canvas.
 * It supports a fixed animation speed and two glow segments.
 * This was made/designed and owned by #Baftes on discord
 */

/**
 * Initializes and manages a chasing glow animation around a target HTML element.
 * @param {string} canvasId - The ID of the canvas element to draw the glow on.
 * @param {string} targetSelector - The CSS selector for the HTML element the glow should wrap around.
 * @param {string} glowColor - The CSS color string for the glow (e.g., 'rgba(0, 247, 255, 1)').
 * @param {number} glowThickness - The thickness of the glow line.
 * @param {number} glowBlur - The blur radius for the glow effect.
 * @param {number} animationSpeed - The speed of the glow animation (e.0.015 for a consistent speed).
 * @param {number} glowLength - The proportional length of each glow segment (0 to 1).
 */
function createChasingGlow(canvasId, targetSelector, glowColor, glowThickness, glowBlur, animationSpeed, glowLength = 0.15) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        console.error(`Canvas with ID '${canvasId}' not found.`);
        return;
    }
    const ctx = canvas.getContext('2d');
    const targetElement = document.querySelector(targetSelector);

    if (!targetElement) {
        console.error(`Target element with selector '${targetSelector}' not found.`);
        return;
    }

    let animationFrameId;
    let pathProgress = 0; // Current position along the path (0 to 1)
    let pathPoints = [];
    let lastTargetWidth = 0;
    let lastTargetHeight = 0;

    /**
     * Resizes and repositions the canvas to perfectly wrap the target element
     * with extra space for the glow blur. This version assumes canvas is a sibling
     * of the targetElement.
     */
    function resizeCanvas() {
        const currentTargetRect = targetElement.getBoundingClientRect();
        const targetWidth = currentTargetRect.width;
        const targetHeight = currentTargetRect.height;
        // MODIFIED: Increased extraSpace multiplier for more buffer, especially vertically.
        const extraSpace = glowThickness * 8; // Increased from 6 to 8

        canvas.width = Math.ceil(targetWidth + extraSpace);
        canvas.height = Math.ceil(targetHeight + extraSpace); 

        // Calculate position relative to the document
        const elemTop = currentTargetRect.top + window.scrollY;
        const elemLeft = currentTargetRect.left + window.scrollX;

        // Position the canvas explicitly based on the target element's position relative to the document.
        canvas.style.position = 'absolute';
        canvas.style.left = `${elemLeft - extraSpace / 2}px`;
        canvas.style.top = `${elemTop - extraSpace / 2}px`;
        canvas.style.zIndex = '1'; 
        canvas.style.pointerEvents = 'none'; 
    }

    /**
     * Generates points along the rounded rectangle path of the target element.
     */
    function getPathPoints(x, y, width, height, radius, segments = 50) {
        const points = [];
        const topRightArc = { cx: x + width - radius, cy: y + radius, startAngle: Math.PI * 1.5, endAngle: Math.PI * 2 };
        const bottomRightArc = { cx: x + width - radius, cy: y + height - radius, startAngle: 0, endAngle: Math.PI * 0.5 };
        const bottomLeftArc = { cx: x + radius, cy: y + height - radius, startAngle: Math.PI * 0.5, endAngle: Math.PI };
        const topLeftArc = { cx: x + radius, cy: y + radius, startAngle: Math.PI, endAngle: Math.PI * 1.5 };

        function addLinePoints(x1, y1, x2, y2, numSegments) {
            const dx = x2 - x1;
            const dy = y2 - y1;
            for (let i = 0; i <= numSegments; i++) {
                points.push({ x: x1 + (dx * i / numSegments), y: y1 + (dy * i / numSegments) });
            }
        }

        function addArcPoints(arc, numSegments) {
            const angleRange = arc.endAngle - arc.startAngle;
            for (let i = 0; i <= numSegments; i++) {
                const angle = arc.startAngle + (angleRange * i / numSegments);
                points.push({ x: arc.cx + radius * Math.cos(angle), y: arc.cy + radius * Math.sin(angle) });
            }
        }

        const segsPerSide = Math.floor(segments / 4);
        addLinePoints(x + radius, y, x + width - radius, y, segsPerSide); 
        addArcPoints(topRightArc, segsPerSide);                               
        addLinePoints(x + width, y + radius, x + width, y + height - radius, segsPerSide); 
        addArcPoints(bottomRightArc, segsPerSide);                            
        addLinePoints(x + width - radius, y + height, x + radius, y + height, segsPerSide); 
        addArcPoints(bottomLeftArc, segsPerSide);                             
        addLinePoints(x, y + height - radius, x, y + radius, segsPerSide); 
        addArcPoints(topLeftArc, segsPerSide);                                

        return points;
    }

    /**
     * Draws the two chasing glow segments on the canvas.
     */
    function drawGlow() {
        ctx.clearRect(0, 0, canvas.width, canvas.height); 

        // Always re-check position and size on every frame
        const currentTargetRect = targetElement.getBoundingClientRect();
        const elemTop = currentTargetRect.top + window.scrollY;
        const elemLeft = currentTargetRect.left + window.scrollX;

        // Re-position canvas if its document-relative position has changed
        const currentCanvasTop = parseFloat(canvas.style.top);
        const currentCanvasLeft = parseFloat(canvas.style.left);
        const extraSpace = glowThickness * 8; // Match the resizeCanvas extraSpace
        
        if (Math.abs(currentCanvasTop - (elemTop - extraSpace / 2)) > 0.5 || 
            Math.abs(currentCanvasLeft - (elemLeft - extraSpace / 2)) > 0.5 || 
            lastTargetWidth !== currentTargetRect.width || 
            lastTargetHeight !== currentTargetRect.height) 
        {
            resizeCanvas(); 
        }

        const boxDrawX = extraSpace / 2; // Center box drawing within extra space
        const boxDrawY = extraSpace / 2; // Center box drawing within extra space
        const boxRadius = parseInt(getComputedStyle(targetElement).borderRadius) || 0; 

        // Regenerate path points if target dimensions change
        if (pathPoints.length === 0 || lastTargetWidth !== currentTargetRect.width || lastTargetHeight !== currentTargetRect.height) {
            pathPoints = getPathPoints(boxDrawX, boxDrawY, currentTargetRect.width, currentTargetRect.height, boxRadius);
            lastTargetWidth = currentTargetRect.width;
            lastTargetHeight = currentTargetRect.height;
        }

        pathProgress = (pathProgress + animationSpeed) % 1; 

        ctx.filter = `blur(${glowBlur}px)`; 
        ctx.strokeStyle = glowColor;
        ctx.lineWidth = glowThickness;
        ctx.lineCap = 'round'; 

        const drawSegment = (progressOffset) => {
            const currentProgress = (pathProgress + progressOffset) % 1;
            let startIndex = Math.floor(currentProgress * pathPoints.length);
            let endIndex = Math.floor((currentProgress + glowLength) * pathPoints.length);

            ctx.beginPath();
            let firstPoint = true;

            for (let i = startIndex; i < endIndex; i++) {
                const pointIndex = i % pathPoints.length;
                const point = pathPoints[pointIndex];
                if (firstPoint) {
                    ctx.moveTo(point.x, point.y);
                    firstPoint = false;
                } else {
                    ctx.lineTo(point.x, point.y);
                }
            }
            if (endIndex > pathPoints.length) {
                for (let i = 0; i < endIndex % pathPoints.length; i++) {
                    const point = pathPoints[i];
                    ctx.lineTo(point.x, point.y);
                }
            }
            ctx.stroke();
        };

        drawSegment(0);
        drawSegment(0.5);

        ctx.filter = 'none'; 
        animationFrameId = requestAnimationFrame(drawGlow);
    }

    let initAttempts = 0;
    const maxInitAttempts = 180; 

    function initGlowWhenReady() {
        if (initAttempts >= maxInitAttempts) {
            console.warn(`Glow initialization timed out for ${targetSelector}. Element may not have appeared or has zero dimensions after ${maxInitAttempts} attempts.`);
            return; 
        }

        const currentTargetRect = targetElement.getBoundingClientRect();
        if (targetElement.offsetWidth > 0 && targetElement.offsetHeight > 0 &&
            currentTargetRect.width > 0 && currentTargetRect.height > 0) {
            console.log(`Dimensions found for ${targetSelector}. Initializing glow.`);
            resizeCanvas(); 
            drawGlow(); 
            
            window.dispatchEvent(new Event('resize')); 
        } else {
            console.log(`Attempt ${initAttempts + 1}: Waiting for dimensions for ${targetSelector}. Current Offset: ${targetElement.offsetWidth}x${targetElement.offsetHeight}, Bounding Rect: ${currentTargetRect.width.toFixed(2)}x${currentTargetRect.height.toFixed(2)}`);
            initAttempts++;
            requestAnimationFrame(initGlowWhenReady);
        }
    }

    window.addEventListener('resize', () => {
        console.log(`Window resized. Re-initializing glow for ${targetSelector}.`);
        cancelAnimationFrame(animationFrameId); 
        resizeCanvas();
        drawGlow(); 
    });

    const observer = new MutationObserver((mutationsList) => {
        const relevantChange = mutationsList.some(mutation =>
            (mutation.type === 'attributes' && (mutation.attributeName === 'style' || mutation.attributeName === 'class')) ||
            mutation.type === 'childList' 
        );
        if (relevantChange) {
            console.log(`Target element changed. Re-initializing glow for ${targetSelector}.`);
            cancelAnimationFrame(animationFrameId);
            resizeCanvas();
            drawGlow();
        }
    });
    observer.observe(targetElement, { attributes: true, childList: true, subtree: true, attributeFilter: ['style', 'class'] });

    window.addEventListener('load', initGlowWhenReady); 
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initGlowWhenReady();
    }
}
