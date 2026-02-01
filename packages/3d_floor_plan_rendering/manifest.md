# 3D Floor Plan Rendering – Manifest

name: 3D Floor Plan Rendering
version: 0.1
domain: architectural visualization / interior design / 2d to 3d conversion
default_prompt: floor_plan_rendering_intent

description:
A specialized cognitive instrument designed to interpret 2D floor plans and generate stylized, furnished 3D sketches. It leverages advanced multimodal understanding to transform architectural drawings into visually rich conceptualizations, abstracting complex interpretation and rendering into a single, user-friendly action.

actions:
  generate_3d_floor_plan:
    description: "Takes a 2D floor plan image and user style preferences, then interprets the plan, intelligently furnishes it, and renders a stylized 3D sketch in a single orchestrated flow."
    template: "floor_plan_rendering_intent"
    handler: "orchestrator_agent" # This would be an internal agent that calls the sub-actions
    context_required: null # The user provides input for the whole flow here
    query_required: true # The query would contain the image URL and style preferences
    updates_context: "lastGenerated3DPlan" # The output of the entire flow

  render:
    description: "Renders the last generated 3D floor plan."
    handler: "image_renderer"
    context_required: "lastGenerated3DPlan"

# Additional configurations can be added here as needed, e.g., for specific rendering quality presets.
# For example, if we wanted to enforce a consistent rendering style across all generated plans:
# rendering_style_lock:
#   enforce_perspective: "3_4_axonometric"
#   enforce_lighting: "soft_daylight"
#   enforce_furnishing_density: "medium"