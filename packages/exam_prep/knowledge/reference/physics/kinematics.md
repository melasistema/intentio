# Physics Reference: Mechanics & Kinematics

## 1. Linear Motion (The Big 4 Equations)
These equations describe the motion of objects (from cars to motor proteins) under **constant acceleration** ($a$).

1.  **Velocity-Time:** $v_f = v_i + at$
2.  **Displacement-Time:** $\Delta x = v_i t + \frac{1}{2}at^2$
3.  **Velocity-Displacement:** $v_f^2 = v_i^2 + 2a\Delta x$
4.  **Average Velocity:** $\Delta x = \frac{v_i + v_f}{2} \cdot t$

### Cognitive Hook: Motor Protein Application
- **Step Size:** Kinesin "walks" in discrete steps of roughly $8\text{ nm}$.
- **Scenario:** If a vesicle starts from rest ($v_i = 0$) and reaches a velocity of $800\text{ nm/s}$ over a distance of $16\text{ nm}$, use Equation 3 to find the **acceleration** provided by the motor protein.

## 2. Dynamics and Force ($F = ma$)
Force is required to change the state of motion of a mass ($m$).

- **Newton's Second Law:** $F = ma$
- **Drag Force (Stokes' Law):** $F_d = 6\pi \eta r v$
    - *Where:* $\eta$ is fluid viscosity, $r$ is particle radius, and $v$ is velocity.
    - **Bio-Link:** Cells are crowded. Use this to calculate the "Friction" an organelle faces in the cytoplasm.

### Cognitive Hook: The "Stall Force"
- **Definition:** The opposing force at which a motor protein can no longer move.
- **Value:** For a single kinesin molecule, $F_{stall} \approx 7\text{ pN}$ (piconewtons).

## 3. Work and Energy
- **Work ($W$):** $W = F \cdot d \cdot \cos(\theta)$
    - *Units:* Joules ($J$) or piconewton-nanometers ($pN \cdot nm$).
- **Kinetic Energy ($KE$):** $KE = \frac{1}{2}mv^2$
- **Potential Energy (Chemical):** $G$ (Gibbs Free Energy).

### Cognitive Hook: Efficiency Calculation
- **Chemistry Link:** One ATP molecule provides $\approx 80\text{ to } 100\text{ pN} \cdot \text{nm}$ of energy.
- **Problem:** If a motor protein does $50\text{ pN} \cdot \text{nm}$ of **Work** to move a cargo, calculate the efficiency ($\frac{W_{out}}{E_{in}}$) based on the ATP values in `chemistry/periodic_table.md`.