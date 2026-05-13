import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, UrlTree } from '@angular/router';
import { PermissionService } from '../services/permission.service';

@Injectable()
export class PermissionGuard implements CanActivate {
  constructor(
    private readonly permissionService: PermissionService,
    private readonly router: Router,
  ) {}

  canActivate(route: ActivatedRouteSnapshot): boolean | UrlTree {
    const requiredPermission = route.data['permission'] as string | undefined;
    if (!requiredPermission) {
      return true;
    }

    return this.permissionService.can(requiredPermission)
      ? true
      : this.router.createUrlTree(['/dashboard']);
  }
}

